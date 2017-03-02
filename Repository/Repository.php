<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Repository;

use Elastica\Aggregation as ElasticaAggregation;
use Elastica\Query as ElasticaQuery;
use Elastica\Result as ElasticaResult;

use Mmoreram\SearchBundle\Elastica\ElasticaWrapper;
use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Tag;
use Mmoreram\SearchBundle\Query\Aggregation as QueryAggregation;
use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\SortBy;
use Mmoreram\SearchBundle\Result\Aggregation as ResultAggregation;
use Mmoreram\SearchBundle\Result\Aggregations as ResultAggregations;
use Mmoreram\SearchBundle\Result\Result;

/**
 * Class Repository.
 */
class Repository
{
    /**
     * @var ElasticaWrapper
     *
     * Elastica wrapper
     */
    private $elasticaWrapper;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper $elasticaWrapper
     */
    public function __construct(ElasticaWrapper $elasticaWrapper)
    {
        $this->elasticaWrapper = $elasticaWrapper;
    }

    /**
     * Search cross the index types.
     *
     * @param string $user
     * @param Query  $query
     *
     * @return Result
     */
    public function search(
        string $user,
        Query $query
    ) : Result {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();

        $boolQuery->addFilter(
            new ElasticaQuery\Term(['user' => $user])
        );

        $this->addFilters(
            $boolQuery,
            $query->getFilters(),
            null,
            false
        );

        $mainQuery->setQuery($boolQuery);
        $mainQuery->setSort(
            $this->addSortBys($query->getSorts())
        );

        $this->addAggregations(
            $mainQuery,
            $query->getAggregations(),
            $query->getFilters()
        );

        $results = $this
            ->elasticaWrapper
            ->search(
                $mainQuery,
                $query->getFrom(),
                $query->getSize()
            );

        return $this->elasticaResultToResult(
            $query,
            $user,
            $results
        );
    }

    /**
     * Build a Result object given elastica result object.
     *
     * @param Query  $query
     * @param string $user
     * @param array  $elasticaResults
     *
     * @return Result
     */
    private function elasticaResultToResult(
        Query $query,
        string $user,
        array $elasticaResults
    ) : Result {
        $resultAggregations = $elasticaResults['aggregations']['all']['all_products'];
        $commonAggregations = $this->getCommonAggregations($resultAggregations);
        unset($resultAggregations['common']);

        $result = new Result(
            $elasticaResults['aggregations']['all']['doc_count'],
            $elasticaResults['aggregations']['all']['all_products']['doc_count'],
            $elasticaResults['total_hits'],
            $commonAggregations['min_price'],
            $commonAggregations['max_price']
        );

        /**
         * @var ElasticaResult $elasticaResult
         */
        foreach ($elasticaResults['results'] as $elasticaResult) {
            $source = $elasticaResult->getSource();
            $source['id'] = str_replace("$user~~", '', $elasticaResult->getId());
            switch ($elasticaResult->getType()) {
                case Product::TYPE:
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
                    $result->addBrand(
                        Brand::createFromArray($source)
                    );
                    break;
                case Tag::TYPE:
                    $result->addTag(
                        Tag::createFromArray($source)
                    );
                    break;
            }
        }

        $aggregations = new ResultAggregations($resultAggregations['doc_count']);
        unset($resultAggregations['doc_count']);

        foreach ($resultAggregations as $aggregationName => $resultAggregation) {
            $queryAggregation = $query->getAggregation($aggregationName);
            $relatedFilter = $query->getFilter($aggregationName);
            $relatedFilterValues = $relatedFilter instanceof Filter
                ? $relatedFilter->getValues()
                : [];

            $elementsToTakeInAccount = $relatedFilter instanceof Filter && $relatedFilter->getApplicationType() & Filter::MUST_ALL_WITH_LEVELS
                ? $relatedFilterValues
                : [];

            $aggregation = new ResultAggregation(
                $aggregationName,
                $queryAggregation->getType(),
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
                        $bucket['doc_count'],
                        $elementsToTakeInAccount
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
            if ($queryAggregation->getType() & Filter::MUST_ALL_WITH_LEVELS) {
                $aggregation->cleanCountersByLevel();
            }
        }
        $result->setAggregations($aggregations);

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
        if ($filter->getFilterType() === Filter::TYPE_RANGE) {
            $boolQuery->addFilter(
                $this->createPriceRangeFilter($filter)
            );

            return;
        }

        if ($filter->getFilterType() === Filter::TYPE_QUERY) {
            $queryString = $filter->getValues()[0];
            $boolQuery->addMust(
                empty($queryString)
                    ? new ElasticaQuery\MatchAll()
                    : new ElasticaQuery\Match('_all', $queryString)
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
        return $filter->getApplicationType() & Filter::MUST_ALL
            ? $this
                ->createQueryFilterMustAll(
                    $filter,
                    $onlyAddDefinedTermFilter,
                    $takeInAccountDefinedTermFilter
                )
            : $this
                ->createQueryFilterAtLeastOne(
                    $filter,
                    $onlyAddDefinedTermFilter,
                    $takeInAccountDefinedTermFilter
                );
    }

    /**
     * Creates a filter where all elements must match.
     *
     * @param Filter $filter
     * @param bool   $onlyAddDefinedTermFilter
     * @param bool   $takeInAccountDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterMustAll(
        Filter $filter,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) : ElasticaQuery\AbstractQuery {
        return $this->createQueryFilterByMethod(
            $filter,
            'addMust',
            $onlyAddDefinedTermFilter,
            $takeInAccountDefinedTermFilter
        );
    }

    /**
     * Creates a filter where, at least, one element should match.
     *
     * @param Filter $filter
     * @param bool   $onlyAddDefinedTermFilter
     * @param bool   $takeInAccountDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterAtLeastOne(
        Filter $filter,
        bool $onlyAddDefinedTermFilter,
        bool $takeInAccountDefinedTermFilter
    ) : ElasticaQuery\AbstractQuery {
        return $this->createQueryFilterByMethod(
            $filter,
            'addShould',
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
        return $filter->getFilterType() === Filter::TYPE_NESTED
            ? $this->createdNestedTermFilter(
                $filter,
                $value
            )
            : $this->createTermFilter(
                $filter,
                $value
            );
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
     * Create PriceRange filter.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    public function createPriceRangeFilter(Filter $filter) : ElasticaQuery\AbstractQuery
    {
        $range = $filter->getValues();
        $priceRangeData = [
            'gte' => $range['from'],
        ];

        if ($range['to'] !== PriceRange::INFINITE) {
            $priceRangeData['lte'] = $range['to'];
        }

        return new ElasticaQuery\Range('real_price', $priceRangeData);
    }

    /**
     * Add a set of sortBy instances to query.
     *
     * @param SortBy[] $sortBys
     *
     * @return array
     */
    private function addSortBys(array $sortBys) : array
    {
        $sorts = [];
        foreach ($sortBys as $sortBy) {
            $sorts = array_merge(
                $sorts,
                [$sortBy]
            );
        }

        return $sorts;
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
            $elasticaAggregation = $aggregation->isNested()
                ? $this->createNestedAggregation($aggregation)
                : $this->createAggregation($aggregation);

            $filteredAggregation = new ElasticaAggregation\Filter($aggregation->getName());
            $boolQuery = new ElasticaQuery\BoolQuery();
            $this->addFilters(
                $boolQuery,
                $filters,
                $aggregation->getType() & Filter::AT_LEAST_ONE
                    ? $aggregation->getName()
                    : null,
                true
            );

            $filteredAggregation->setFilter($boolQuery);
            $filteredAggregation->addAggregation($elasticaAggregation);
            $productsAggregation->addAggregation($filteredAggregation);
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
        $fields = array_map(function ($field) {
            return "doc['{$field}'].value";
        }, explode('|', $aggregation->getField()));

        $termsAggregation->setScript(implode(' + "~~" + ', $fields));

        return $termsAggregation;
    }
}
