<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Config\Campaign;
use Apisearch\Config\Campaigns;
use Apisearch\Geo\CoordinateAndDistance;
use Apisearch\Geo\LocationRange;
use Apisearch\Geo\Polygon;
use Apisearch\Geo\Square;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Aggregation as QueryAggregation;
use Apisearch\Query\Filter;
use Apisearch\Query\Query;
use Apisearch\Query\Range;
use Apisearch\Query\SortBy;
use Apisearch\Result\Aggregation as ResultAggregation;
use Apisearch\Result\Aggregations as ResultAggregations;
use Apisearch\Result\Result;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Carbon\Carbon;
use Elastica\Aggregation as ElasticaAggregation;
use Elastica\Query as ElasticaQuery;
use Elastica\Suggest;
use Exception;

/**
 * Class QueryRepository.
 */
class QueryRepository extends ElasticaWrapperWithRepositoryReference
{
    /**
     * Search cross the index types.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function query(Query $query): Result
    {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();

        $this->addFilters(
            $boolQuery,
            $query->getFilters(),
            $query->getFilterFields(),
            null,
            false
        );

        $this->addFilters(
            $boolQuery,
            $query->getUniverseFilters(),
            $query->getFilterFields(),
            null,
            false
        );

        $this->promoteUUIDs(
            $boolQuery,
            $query->getItemsPromoted()
        );

        if ($query->areHighlightEnabled()) {
            $this->addHighlights($mainQuery);
        }

        $this->addCampaigns($query, $boolQuery);
        $mainQuery->setQuery($boolQuery);

        if (SortBy::SCORE !== $query->getSortBy()) {
            if (SortBy::RANDOM === $query->getSortBy()) {
                /**
                 * Random elements in Elasticsearch need a wrapper in order to
                 * apply a random score per each result.
                 */
                $functionScore = new ElasticaQuery\FunctionScore();
                $functionScore->addRandomScoreFunction(uniqid());
                $functionScore->setQuery($boolQuery);
                $mainQuery = new ElasticaQuery();
                $mainQuery->setQuery($functionScore);
            } else {
                $mainQuery->setSort($query->getSortBy());
            }
        }

        if ($query->areAggregationsEnabled()) {
            $this->addAggregations(
                $mainQuery,
                $query->getAggregations(),
                $query->getUniverseFilters(),
                $query->getFilters(),
                $query->getFilterFields()
            );
        }

        $this->addSuggest(
            $mainQuery,
            $query
        );

        $results = $this
            ->elasticaWrapper
            ->search(
                $this->getRepositoryReference(),
                $mainQuery,
                $query->areResultsEnabled()
                    ? $query->getFrom()
                    : 0,
                $query->areResultsEnabled()
                    ? $query->getSize()
                    : 0
            );

        return $this->elasticaResultToResult(
            $query,
            $results
        );
    }

    /**
     * Build a Result object given elastica result object.
     *
     * @param Query $query
     * @param array $elasticaResults
     *
     * @return Result
     */
    private function elasticaResultToResult(
        Query $query,
        array $elasticaResults
    ): Result {
        /*
         * @TODO Move this if/else into another place
         */
        if ($query->areAggregationsEnabled()) {
            $resultAggregations = $elasticaResults['aggregations']['all']['universe'];
            unset($resultAggregations['common']);

            $result = new Result(
                $query,
                $elasticaResults['aggregations']['all']['universe']['doc_count'],
                $elasticaResults['total_hits']
            );
        } else {
            $result = new Result(
                $query,
                0,
                $elasticaResults['total_hits']
            );
        }

        /*
         * @var ElasticaResult
         */
        foreach ($elasticaResults['items'] as $elasticaResult) {
            $source = $elasticaResult->getSource();
            if (
                isset($elasticaResult->getParam('sort')[0]) &&
                is_float($elasticaResult->getParam('sort')[0])
            ) {
                $source['distance'] = $elasticaResult->getParam('sort')[0];
            }

            $item = Item::createFromArray($source);

            if ($query->areHighlightEnabled()) {
                $formedHighlights = [];
                foreach ($elasticaResult->getHighlights() as $highlightField => $highlightValue) {
                    $formedHighlights[str_replace('searchable_metadata.', '', $highlightField)] = $highlightValue[0];
                }

                $item->setHighlights($formedHighlights);
            }

            $result->addItem($item);
        }

        /*
         * @TODO Move this part into another place
         */
        if ($query->areAggregationsEnabled()) {
            $aggregations = new ResultAggregations($resultAggregations['doc_count']);
            unset($resultAggregations['doc_count']);
            foreach ($resultAggregations as $aggregationName => $resultAggregation) {
                $queryAggregation = $query->getAggregation($aggregationName);
                $relatedFilter = $query->getFilter($aggregationName);
                $relatedFilterValues = $relatedFilter instanceof Filter
                    ? $relatedFilter->getValues()
                    : [];

                $aggregation = new ResultAggregation(
                    $aggregationName,
                    $queryAggregation->getApplicationType(),
                    $resultAggregation['doc_count'],
                    $relatedFilterValues
                );

                $aggregations->addAggregation($aggregationName, $aggregation);
                $buckets = isset($resultAggregation[$aggregationName]['buckets'])
                    ? $resultAggregation[$aggregationName]['buckets']
                    : $resultAggregation[$aggregationName][$aggregationName]['buckets'];

                if (empty($buckets)) {
                    continue;
                }

                foreach ($buckets as $key => $bucket) {
                    $usedKey = $bucket['key'] ?? $key;
                    if (
                        empty($queryAggregation->getSubgroup()) ||
                        in_array($usedKey, $queryAggregation->getSubgroup())
                    ) {
                        $aggregation->addCounter(
                            (string) $usedKey,
                            (int) $bucket['doc_count']
                        );
                    }
                }

                /*
                 * We should filter the bucket elements with level that are not part
                 * of the result.
                 *
                 * * Filter type MUST_ALL
                 * * Elements already filtered
                 * * Elements with level (if exists) than the highest one
                 */
                if (Filter::MUST_ALL_WITH_LEVELS === $queryAggregation->getApplicationType()) {
                    $aggregation->cleanCountersByLevel();
                }
            }
            $result->setAggregations($aggregations);
        }

        /*
         * @TODO Move this part into another place
         */
        if (isset($elasticaResults['suggests']['completion']) && $query->areSuggestionsEnabled()) {
            foreach ($elasticaResults['suggests']['completion'][0]['options'] as $suggest) {
                $result->addSuggest($suggest['text']);
            }
        }

        return $result;
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter[]                $filters
     * @param string[]                $filterFields
     * @param null|string             $filterToIgnore
     * @param bool                    $takeInAccountDefinedTermFilter
     */
    private function addFilters(
        ElasticaQuery\BoolQuery $boolQuery,
        array $filters,
        array $filterFields,
        ? string $filterToIgnore,
        bool $takeInAccountDefinedTermFilter
    ) {
        foreach ($filters as $filterName => $filter) {
            $onlyAddDefinedTermFilter = (
                empty($filter->getValues()) ||
                $filterName === $filterToIgnore
            );

            $this->addFilter(
                $boolQuery,
                $filter,
                $filterFields,
                $onlyAddDefinedTermFilter,
                $takeInAccountDefinedTermFilter
            );
        }
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter                  $filter
     * @param string[]                $filterFields
     * @param bool                    $onlyAddDefinedTermFilter
     * @param bool                    $takeInAccountDefinedTermFilter
     */
    private function addFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter,
        array $filterFields,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) {
        if (Filter::TYPE_QUERY === $filter->getFilterType()) {
            $queryString = $filter->getValues()[0];

            if (empty($queryString)) {
                $match = new ElasticaQuery\MatchAll();
            } else {
                $match = new ElasticaQuery\MultiMatch();
                $filterFields = empty($filterFields)
                    ? [
                        'searchable_metadata.*',
                        'exact_matching_metadata^5',
                    ]
                    : $filterFields;

                $match
                    ->setFields($filterFields)
                    ->setQuery($queryString)
                    ->setType('phrase');
            }
            $boolQuery->addShould($match);

            return;
        }

        if (Filter::TYPE_GEO === $filter->getFilterType()) {
            $boolQuery->addMust(
                $this->createLocationFilter($filter)
            );

            return;
        }

        $boolQuery->addFilter(
            $this->createQueryFilterByApplicationType(
                $filter,
                $onlyAddDefinedTermFilter,
                $takeInAccountDefinedTermFilter
            )
        );
    }

    /**
     * Create a filter and decide type of match.
     *
     * @param Filter $filter
     * @param bool   $onlyAddDefinedTermFilter
     * @param bool   $takeInAccountDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterByApplicationType(
        Filter $filter,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) {
        $verb = 'addMust';
        switch ($filter->getApplicationType()) {
            case Filter::AT_LEAST_ONE:
                $verb = 'addShould';
                break;
            case Filter::EXCLUDE:
                $verb = 'addMustNot';
                break;
        }

        return $this->createQueryFilterByMethod(
            $filter,
            $verb,
            $onlyAddDefinedTermFilter,
            $takeInAccountDefinedTermFilter
        );
    }

    /**
     * Creates query filter by method.
     *
     * @param Filter $filter
     * @param string $method
     * @param bool   $onlyAddDefinedTermFilter
     * @param bool   $takeInAccountDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterByMethod(
        Filter $filter,
        string $method,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) {
        $boolQueryFilter = new ElasticaQuery\BoolQuery();
        if (!$onlyAddDefinedTermFilter) {
            foreach ($filter->getValues() as $value) {
                $queryFilter = $this->createQueryFilter(
                    $filter,
                    $value
                );

                if ($queryFilter instanceof ElasticaQuery\AbstractQuery) {
                    $boolQueryFilter->$method($queryFilter);
                }
            }
        }

        /*
         * This is specifically for Tags.
         * Because you can make subgroups of Tags, each aggregation must define
         * its values from this given subgroup.
         */
        if ($takeInAccountDefinedTermFilter && !empty($filter->getFilterTerms())) {
            list($field, $value) = $filter->getFilterTerms();
            $filteringFilter = Filter::create(
                $field, $value, Filter::AT_LEAST_ONE, $filter->getFilterType(), []
            );

            $boolQueryFilter->addFilter(
                $this
                    ->createQueryFilterByApplicationType(
                        $filteringFilter,
                        false,
                        false
                    )
            );
        }

        return $boolQueryFilter;
    }

    /**
     * Creates Term/Terms query depending on the elements value.
     *
     * @param Filter $filter
     * @param mixed  $value
     *
     * @return null|ElasticaQuery\AbstractQuery
     */
    private function createQueryFilter(
        Filter $filter,
        $value
    ): ? ElasticaQuery\AbstractQuery {
        switch ($filter->getFilterType()) {
            case Filter::TYPE_FIELD:
                return $this->createTermFilter(
                    $filter,
                    $value
                );
                break;

            case Filter::TYPE_RANGE:
            case Filter::TYPE_DATE_RANGE:
                return $this->createRangeFilter(
                    $filter,
                    $value
                );
                break;
        }
    }

    /**
     * Create and return Term filter
     * Returns null if no need to be applicable (true=true).
     *
     * @param Filter $filter
     * @param mixed  $value
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createTermFilter(
        Filter $filter,
        $value
    ): ? ElasticaQuery\AbstractQuery {
        return $this->createMultipleTermFilter($filter->getField(), $value);
    }

    /**
     * Create multiple Term filter.
     *
     * @param string          $field
     * @param string|string[] $value
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createMultipleTermFilter(
        string $field,
        $value
    ): ElasticaQuery\AbstractQuery {
        if (!is_array($value)) {
            return new ElasticaQuery\Term([$field => $value]);
        }

        $multipleBoolQuery = new ElasticaQuery\BoolQuery();
        foreach ($value as $singleValue) {
            $multipleBoolQuery->addShould(
                new ElasticaQuery\Term([$field => $singleValue])
            );
        }

        return $multipleBoolQuery;
    }

    /**
     * Create Range filter.
     *
     * @param Filter $filter
     * @param string $value
     *
     * @return null|ElasticaQuery\AbstractQuery
     */
    private function createRangeFilter(
        Filter $filter,
        string $value
    ): ? ElasticaQuery\AbstractQuery {
        list($from, $to) = Range::stringToArray($value);
        $rangeData = [];
        if ($from > Range::ZERO) {
            $rangeData = [
                'gte' => $from,
            ];
        }

        if (Range::INFINITE !== $to) {
            $rangeData['lt'] = $to;
        }

        $rangeClass = Filter::TYPE_DATE_RANGE === $filter->getFilterType()
            ? ElasticaQuery\Range::class
            : ElasticaQuery\Range::class;

        return empty($rangeData)
            ? null
            : new $rangeClass($filter->getField(), $rangeData);
    }

    /**
     * Create Location filter.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createLocationFilter(Filter $filter): ElasticaQuery\AbstractQuery
    {
        $locationRange = LocationRange::createFromArray($filter->getValues());
        $locationRangeData = $locationRange->toFilterArray();
        switch (get_class($locationRange)) {
            case CoordinateAndDistance::class:

                return new ElasticaQuery\GeoDistance(
                    $filter->getField(),
                    $locationRangeData['coordinate'],
                    $locationRangeData['distance']
                );

            case Polygon::class:

                return new ElasticaQuery\GeoPolygon(
                    $filter->getField(),
                    $locationRangeData
                );

            case Square::class:

                return new ElasticaQuery\GeoBoundingBox(
                    $filter->getField(),
                    $locationRangeData
                );
        }
    }

    /**
     * Add aggregations.
     *
     * @param ElasticaQuery      $elasticaQuery
     * @param QueryAggregation[] $aggregations
     * @param Filter[]           $universeFilters
     * @param Filter[]           $filters
     * @param string[]           $filterFields
     */
    private function addAggregations(
        ElasticaQuery $elasticaQuery,
        array $aggregations,
        array $universeFilters,
        array $filters,
        array $filterFields
    ) {
        $globalAggregation = new ElasticaAggregation\GlobalAggregation('all');
        $universeAggregation = new ElasticaAggregation\Filter('universe');
        $aggregationBoolQuery = new ElasticaQuery\BoolQuery();
        $this->addFilters(
            $aggregationBoolQuery,
            $universeFilters,
            $filterFields,
            null,
            true
        );
        $universeAggregation->setFilter($aggregationBoolQuery);
        $globalAggregation->addAggregation($universeAggregation);

        foreach ($aggregations as $aggregation) {
            $filterType = $aggregation->getFilterType();
            switch ($filterType) {
                case Filter::TYPE_RANGE:
                case Filter::TYPE_DATE_RANGE:
                    $elasticaAggregation = $this->createRangeAggregation($aggregation);
                    break;
                default:
                    $elasticaAggregation = $this->createAggregation($aggregation);
                    break;
            }

            $filteredAggregation = new ElasticaAggregation\Filter($aggregation->getName());
            $boolQuery = new ElasticaQuery\BoolQuery();
            $this->addFilters(
                $boolQuery,
                $filters,
                $filterFields,
                $aggregation->getApplicationType() & Filter::AT_LEAST_ONE
                    ? $aggregation->getName()
                    : null,
                true
            );

            $filteredAggregation->setFilter($boolQuery);
            $filteredAggregation->addAggregation($elasticaAggregation);
            $universeAggregation->addAggregation($filteredAggregation);
        }

        $elasticaQuery->addAggregation($globalAggregation);
    }

    /**
     * Create aggregation.
     *
     * @param QueryAggregation $aggregation
     *
     * @return ElasticaAggregation\AbstractAggregation
     */
    private function createAggregation(QueryAggregation $aggregation): ElasticaAggregation\AbstractAggregation
    {
        $termsAggregation = new ElasticaAggregation\Terms($aggregation->getName());
        $aggregationFields = explode('|', $aggregation->getField());
        $termsAggregation->setField($aggregationFields[0]);
        $termsAggregation->setSize(
            $aggregation->getLimit() > 0
                ? $aggregation->getLimit()
                : 1000
        );
        $termsAggregation->setOrder($aggregation->getSort()[0], $aggregation->getSort()[1]);

        return $termsAggregation;
    }

    /**
     * Create range aggregation.
     *
     * @param QueryAggregation $aggregation
     *
     * @return ElasticaAggregation\AbstractAggregation
     */
    private function createRangeAggregation(QueryAggregation $aggregation): ElasticaAggregation\AbstractAggregation
    {
        $rangeClass = Filter::TYPE_DATE_RANGE === $aggregation->getFilterType()
            ? ElasticaAggregation\DateRange::class
            : ElasticaAggregation\Range::class;

        $rangeAggregation = new $rangeClass($aggregation->getName());
        $rangeAggregation->setKeyedResponse();
        $rangeAggregation->setField($aggregation->getField());
        foreach ($aggregation->getSubgroup() as $range) {
            list($from, $to) = Range::stringToArray($range);
            $rangeAggregation->addRange($from, $to, $range);
        }

        return $rangeAggregation;
    }

    /**
     * Add suggest into an Elastica Query.
     *
     * @param ElasticaQuery $mainQuery
     * @param Query         $query
     */
    private function addSuggest($mainQuery, $query)
    {
        if ($query->areSuggestionsEnabled()) {
            $completitionText = new Suggest\Completion(
                'completion',
                'suggest'
            );
            $completitionText->setText($query->getQueryText());

            $mainQuery->setSuggest(
                new Suggest($completitionText)
            );
        }
    }

    /**
     * Promote UUID.
     *
     * The boosting values go from 1 (not included) to 3 (not included)
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param ItemUUID[]              $itemsPriorized
     */
    private function promoteUUIDs(
        ElasticaQuery\BoolQuery $boolQuery,
        array $itemsPriorized
    ) {
        if (empty($itemsPriorized)) {
            return;
        }

        $it = 1;
        foreach ($itemsPriorized as $position => $itemUUID) {
            $boolQuery->addShould(new ElasticaQuery\Term([
                '_id' => [
                    'value' => $itemUUID->composeUUID(),
                    'boost' => 10 + ($it++ / (count($itemsPriorized) + 1)),
                ],
            ]));
        }
    }

    /**
     * Highlight.
     *
     * @param ElasticaQuery $query
     */
    private function addHighlights(ElasticaQuery $query)
    {
        $query->setHighlight([
            'fields' => [
                '*' => [
                    'fragment_size' => 100,
                    'number_of_fragments' => 3,
                ],
            ],
        ]);
    }

    /**
     * Add campaigns.
     *
     * @param Query                   $query
     * @param ElasticaQuery\BoolQuery $elasticaQuery
     */
    private function addCampaigns(
        Query $query,
        ElasticaQuery\BoolQuery $elasticaQuery
    ) {
        $file = $this->getConfigPath().'/campaigns.json';
        if (!is_file($file)) {
            return;
        }

        try {
            $campaignsAsArray = json_decode(file_get_contents($file), true);
        } catch (Exception $e) {
            return;
        }

        $now = Carbon::now('UTC')->timestamp;
        $enabledCampaigns = array_filter(
            Campaigns::createFromArray($campaignsAsArray)->getCampaigns(),
            function (Campaign $campaign) use ($query, $now) {
                return
                    $campaign->isEnabled() &&
                    !empty($campaign->getBoostClauses()) &&
                    (
                        is_null($campaign->getFrom()) ||
                        $campaign->getFrom()->getTimestamp() <= $now
                    ) &&
                    (
                        is_null($campaign->getTo()) ||
                        $campaign->getTo()->getTimestamp() > $now
                    ) &&
                    (
                        empty($campaign->getQueryText()) ||
                        (preg_match('~^'.$campaign->getQueryText().'$~i', $query->getQueryText()) === 1)
                    ) &&
                    (
                        empty($campaign->getAppliedFilters()) ||
                        empty(array_filter(
                            $campaign->getAppliedFilters(),
                            function ($values, string $field) use ($query) {
                                $values = is_array($values) ? $values : [$values];
                                $filter = $query->getFilterByField($field);
                                if (is_null($filter)) {
                                    return true;
                                }

                                return !empty(array_intersect($values, $filter->getValues()));
                            },
                            ARRAY_FILTER_USE_BOTH
                        ))
                    )
                ;
            }
        );

        if (empty($enabledCampaigns)) {
            return;
        }

        /**
         * @var Campaign $enabledCampaign
         */
        $boolQuery = new ElasticaQuery\BoolQuery();
        foreach ($enabledCampaigns as $enabledCampaign) {
            foreach ($enabledCampaign->getBoostClauses() as $boostClause) {
                $boosting = $boostClause->getBoost();
                foreach ($boostClause->getValues() as $value) {
                    $term = new ElasticaQuery\Term([
                        Filter::getFilterPathByField($boostClause->getField()) => [
                            'value' => $value,
                            'boost' => $boosting,
                        ],
                    ]);
                    $boolQuery->addShould($term);
                }
            }
        }
        $enabledCampaign->getMode() === Campaign::MODE_BOOST
            ? $elasticaQuery->addShould($boolQuery)
            : $elasticaQuery->addMust($boolQuery);
    }
}
