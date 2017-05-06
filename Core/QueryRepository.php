<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Core;

use Elastica\Aggregation as ElasticaAggregation;
use Elastica\Query as ElasticaQuery;
use Elastica\Result as ElasticaResult;
use Elastica\Suggest;

use Puntmig\Search\Geo\CoordinateAndDistance;
use Puntmig\Search\Geo\LocationRange;
use Puntmig\Search\Geo\Polygon;
use Puntmig\Search\Geo\Square;
use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Query\Aggregation as QueryAggregation;
use Puntmig\Search\Query\Filter;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Query\Range;
use Puntmig\Search\Query\SortBy;
use Puntmig\Search\Result\Aggregation as ResultAggregation;
use Puntmig\Search\Result\Aggregations as ResultAggregations;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Elastica\ElasticaWrapper;

/**
 * Class QueryRepository.
 */
class QueryRepository extends ElasticaWithKeyWrapper
{
    /**
     * Search cross the index types.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function query(Query $query) : Result
    {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();

        $this->addFilters(
            $boolQuery,
            $query->getFilters(),
            null,
            false
        );

        $mainQuery->setQuery($boolQuery);
        if ($query->getSortBy() !== SortBy::SCORE) {
            $mainQuery->setSort($query->getSortBy());
        }

        if ($query->areAggregationsEnabled()) {
            $this->addAggregations(
                $mainQuery,
                $query->getAggregations(),
                $query->getFilters()
            );
        }

        $this->addSuggest(
            $mainQuery,
            $query
        );

        $results = $this
            ->elasticaWrapper
            ->search(
                $this->key,
                $mainQuery,
                $query->getFrom(),
                $query->getSize()
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
    ) : Result {

        /**
         * @TODO Move this if/else into another place
         */
        if ($query->areAggregationsEnabled()) {
            $allProductsAggregation = $elasticaResults['aggregations']['all']['all_products'];
            unset($elasticaResults['aggregations']['all']['all_products']);
            $resultAggregations = $elasticaResults['aggregations']['all'];
            $commonAggregations = $this->getCommonAggregations($allProductsAggregation);
            unset($resultAggregations['common']);

            $result = new Result(
                $elasticaResults['aggregations']['all']['doc_count'],
                $allProductsAggregation['doc_count'],
                $elasticaResults['total_hits'],
                $commonAggregations['min_price'],
                $commonAggregations['max_price'],
                $commonAggregations['price_average'],
                $commonAggregations['rating_average']
            );
        } else {
            $result = new Result(
                0, 0,
                $elasticaResults['total_hits'],
                0, 0, 0, 0
            );
        }

        /**
         * @var ElasticaResult $elasticaResult
         */
        foreach ($elasticaResults['results'] as $elasticaResult) {
            $source = $elasticaResult->getSource();
            switch ($elasticaResult->getType()) {
                case Product::TYPE:

                    /**
                     * We should find a possible distance.
                     */
                    if (
                        isset($elasticaResult->getParam('sort')[0]) &&
                        is_float($elasticaResult->getParam('sort')[0])
                    ) {
                        $source['distance'] = $elasticaResult->getParam('sort')[0];
                    }

                    $result->addProduct(
                        Product::createFromArray($source)
                    );
                    break;
                case Category::TYPE:
                    $result->addCategory(
                        Category::createFromArray($source)
                    );
                    break;
                case Manufacturer::TYPE:
                    $result->addManufacturer(
                        Manufacturer::createFromArray($source)
                    );
                    break;
                case Brand::TYPE:
                    $brand = Brand::createFromArray($source);
                    if ($brand instanceof Brand) {
                        $result->addBrand($brand);
                    }
                    break;
                case Tag::TYPE:
                    $result->addTag(
                        Tag::createFromArray($source)
                    );
                    break;
            }
        }

        /**
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

                foreach ($buckets as $bucket) {
                    if (
                        empty($queryAggregation->getSubgroup()) ||
                        in_array($bucket['key'], $queryAggregation->getSubgroup())
                    ) {
                        $aggregation->addCounter(
                            $bucket['key'],
                            $bucket['doc_count']
                        );
                    }
                }

                /**
                 * We should filter the bucket elements with level that are not part
                 * of the result.
                 *
                 * * Filter type MUST_ALL
                 * * Elements already filtered
                 * * Elements with level (if exists) than the highest one
                 */
                if ($queryAggregation->getApplicationType() & Filter::MUST_ALL_WITH_LEVELS) {
                    $aggregation->cleanCountersByLevel();
                }
            }
            $result->setAggregations($aggregations);
        }

        /**
         * @TODO Move this part into another place
         */
        if ($query->areSuggestionsEnabled()) {
            foreach ($elasticaResults['suggests']['completion'][0]['options'] as $suggest) {
                $result->addSuggest($suggest['text']);
            }
        }

        return $result;
    }

    /**
     * Get common aggregations from ElasticaResult.
     *
     * @param array $elasticaResult
     *
     * @return array
     */
    private function getCommonAggregations(array $elasticaResult) : array
    {
        return [
            'min_price' => (int) $elasticaResult['common']['min_price']['value'],
            'max_price' => (int) $elasticaResult['common']['max_price']['value'],
            'price_average' => (float) $elasticaResult['common']['price_average']['value'],
            'rating_average' => (float) $elasticaResult['common']['rating_average']['value'],
        ];
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter[]                $filters
     * @param null|string             $filterToIgnore
     * @param bool                    $takeInAccountDefinedTermFilter
     */
    private function addFilters(
        ElasticaQuery\BoolQuery $boolQuery,
        array $filters,
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
     * @param bool                    $onlyAddDefinedTermFilter
     * @param bool                    $takeInAccountDefinedTermFilter
     */
    private function addFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) {
        if ($filter->getFilterType() === Filter::TYPE_QUERY) {
            $queryString = $filter->getValues()[0];

            if (empty($queryString)) {
                $match = new ElasticaQuery\MatchAll();
            } else {
                $match = new ElasticaQuery\MultiMatch();
                $match->setFields([
                    'special_words^10',
                    'ean^3',
                    'first_level_searchable_data^2',
                    'second_level_searchable_data^1',
                    'indexed_metadata^1',
                ])->setQuery($queryString);
            }
            $boolQuery->addMust($match);

            return;
        }

        if ($filter->getFilterType() === Filter::TYPE_GEO) {
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
            case Filter::AT_LEAST_ONE :
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
                    (string) $value
                );

                if ($queryFilter instanceof ElasticaQuery\AbstractQuery) {
                    $boolQueryFilter->$method($queryFilter);
                }
            }
        }

        /**
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
     * @param string $value
     *
     * @return null|ElasticaQuery\AbstractQuery
     */
    private function createQueryFilter(
        Filter $filter,
        string $value
    ) : ? ElasticaQuery\AbstractQuery {
        switch ($filter->getFilterType()) {
            case Filter::TYPE_NESTED :
                return $this->createdNestedTermFilter(
                    $filter,
                    $value
                );
            case Filter::TYPE_FIELD:
                return $this->createTermFilter(
                    $filter,
                    $value
                );
            case Filter::TYPE_RANGE:
                return $this->createRangeFilter(
                    $filter,
                    $value
                );
        }
    }

    /**
     * Adds terms filter given a BoolQuery.
     *
     * @param Filter $filter
     * @param string $value
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createdNestedTermFilter(
        Filter $filter,
        string $value
    ) : ElasticaQuery\AbstractQuery {
        list($path, $fieldName) = explode('.', $filter->getField(), 2);

        $nestedQuery = new ElasticaQuery\Nested();
        $nestedQuery->setPath($path);
        $nestedQuery->setScoreMode('max');
        $nestedQuery->setQuery($this->createTermFilter(
            $filter,
            $value
        ));

        return $nestedQuery;
    }

    /**
     * Create and return Term filter
     * Returns null if no need to be applicable (true=true).
     *
     * @param Filter $filter
     * @param string $value
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createTermFilter(
        Filter $filter,
        string $value
    ) : ? ElasticaQuery\AbstractQuery {
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
    ) : ElasticaQuery\AbstractQuery {
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
    ) : ? ElasticaQuery\AbstractQuery {
        list($from, $to) = Range::stringToArray($value);
        $rangeData = [];
        if ($from > Range::ZERO) {
            $rangeData = [
                'gte' => $from,
            ];
        }

        if ($to !== Range::INFINITE) {
            $rangeData['lt'] = $to;
        }

        return empty($rangeData)
            ? null
            : new ElasticaQuery\Range($filter->getField(), $rangeData);
    }

    /**
     * Create Location filter.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createLocationFilter(Filter $filter) : ElasticaQuery\AbstractQuery
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
     * @param Filter[]           $filters
     */
    private function addAggregations(
        ElasticaQuery $elasticaQuery,
        array $aggregations,
        array $filters
    ) {
        $globalAggregation = new ElasticaAggregation\GlobalAggregation('all');
        $productsAggregation = new ElasticaAggregation\Filter('all_products', new ElasticaQuery\Term(['_type' => Product::TYPE]));
        $globalAggregation->addAggregation($productsAggregation);
        foreach ($aggregations as $aggregation) {
            $filterType = $aggregation->getFilterType();
            if ($filterType == Filter::TYPE_RANGE) {
                $elasticaAggregation = $this->createRangeAggregation($aggregation);
            } elseif ($filterType == Filter::TYPE_NESTED) {
                $elasticaAggregation = $this->createNestedAggregation($aggregation);
            } else {
                $elasticaAggregation = $this->createAggregation($aggregation);
            }

            $filteredAggregation = new ElasticaAggregation\Filter($aggregation->getName());
            $boolQuery = new ElasticaQuery\BoolQuery();
            $this->addFilters(
                $boolQuery,
                $filters,
                $aggregation->getApplicationType() & Filter::AT_LEAST_ONE
                    ? $aggregation->getName()
                    : null,
                true
            );

            $filteredAggregation->setFilter($boolQuery);
            $filteredAggregation->addAggregation($elasticaAggregation);
            $globalAggregation->addAggregation($filteredAggregation);
        }

        $elasticaQuery->addAggregation($globalAggregation);
        $this->addCommonAggregations($productsAggregation, $filters);
    }

    /**
     * Add common aggregations.
     *
     * @param ElasticaAggregation\AbstractAggregation $productsAggregation
     * @param Filter[]                                $filters
     */
    private function addCommonAggregations(
        ElasticaAggregation\AbstractAggregation $productsAggregation,
        array $filters
    ) {
        $commonAggregations = new ElasticaAggregation\Filter('common');
        $boolQuery = new ElasticaQuery\BoolQuery();
        $this->addFilters(
            $boolQuery,
            $filters,
            '',
            false
        );
        $commonAggregations->setFilter($boolQuery);

        $minPriceAggregation = new ElasticaAggregation\Min('min_price');
        $minPriceAggregation->setField('real_price');
        $commonAggregations->addAggregation($minPriceAggregation);

        $maxPriceAggregation = new ElasticaAggregation\Max('max_price');
        $maxPriceAggregation->setField('real_price');
        $commonAggregations->addAggregation($maxPriceAggregation);

        $avgPriceAggregation = new ElasticaAggregation\Avg('price_average');
        $avgPriceAggregation->setField('real_price');
        $commonAggregations->addAggregation($avgPriceAggregation);

        $ratingAverageAggregation = new ElasticaAggregation\Avg('rating_average');
        $ratingAverageAggregation->setField('rating');
        $commonAggregations->addAggregation($ratingAverageAggregation);

        $productsAggregation->addAggregation($commonAggregations);
    }

    /**
     * Create nested aggregation.
     *
     * @param QueryAggregation $aggregation
     *
     * @return ElasticaAggregation\AbstractAggregation
     */
    private function createNestedAggregation(QueryAggregation $aggregation) : ElasticaAggregation\AbstractAggregation
    {
        $path = explode('.', $aggregation->getField())[0];
        $nestedAggregation = new ElasticaAggregation\Nested($aggregation->getName(), $path);
        $nestedAggregation->addAggregation(
            $this->createAggregation($aggregation)
        );

        return $nestedAggregation;
    }

    /**
     * Create aggregation.
     *
     * @param QueryAggregation $aggregation
     *
     * @return ElasticaAggregation\AbstractAggregation
     */
    private function createAggregation(QueryAggregation $aggregation) : ElasticaAggregation\AbstractAggregation
    {
        $termsAggregation = new ElasticaAggregation\Terms($aggregation->getName());
        $aggregationFields = explode('|', $aggregation->getField());
        $fields = array_map(function ($field) use (&$oneField) {
            return "doc['{$field}'].value";
        }, $aggregationFields);

        count($aggregationFields) > 1
            ? $termsAggregation->setScript('return ' . implode(' + "~~" + ', $fields))
            : $termsAggregation->setField($aggregationFields[0]);

        return $termsAggregation;
    }

    /**
     * Create range aggregation.
     *
     * @param QueryAggregation $aggregation
     *
     * @return ElasticaAggregation\AbstractAggregation
     */
    private function createRangeAggregation(QueryAggregation $aggregation) : ElasticaAggregation\AbstractAggregation
    {
        $rangeAggregation = new ElasticaAggregation\Range($aggregation->getName());
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
}
