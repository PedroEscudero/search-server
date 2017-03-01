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
use Elastica\Document as ElasticaDocument;
use Elastica\Query as ElasticaQuery;
use Elastica\Result as ElasticaResult;

use Mmoreram\SearchBundle\Elastica\ElasticaWrapper;
use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Query\Aggregation;
use Mmoreram\SearchBundle\Query\Aggregation as QueryAggregation;
use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\SortBy;
use Mmoreram\SearchBundle\Result\Aggregation as ResultAggregation;
use Mmoreram\SearchBundle\Result\Aggregations as ResultAggregations;
use Mmoreram\SearchBundle\Result\Result;

/**
 * Class ElasticaSearchRepository.
 */
class ElasticaSearchRepository implements SearchRepository
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
     * Index product.
     *
     * @param string  $user
     * @param Product $product
     */
    public function index(
        string $user,
        Product $product
    ) {
        $this->indexProduct(
            $user,
            $product
        );
        $this
            ->elasticaWrapper
            ->refresh();
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
            $query->getFilters()
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
     * Private methods.
     */

    /**
     * Index product.
     *
     * @param string  $user
     * @param Product $product
     */
    private function indexProduct(
        string $user,
        Product $product
    ) {
        $productId = "$user~~{$product->getId()}";
        $productDocument = [
            'user' => $user,
            'id' => $productId,
            'family' => $product->getFamily(),
            'ean' => $product->getEan(),
            'name' => $product->getName(),
            'sortable_name' => $product->getName(),
            'description' => $product->getDescription(),
            'long_description' => $product->getLongDescription(),
            'price' => $product->getPrice(),
            'reduced_price' => $product->getReducedPrice(),
            'real_price' => $product->getRealPrice(),
            'discount' => $product->getDiscount(),
            'discount_percentage' => $product->getDiscountPercentage(),
            'first_level_searchable_data' => $product->getFirstLevelSearchableData(),
            'second_level_searchable_data' => $product->getSecondLevelSearchableData(),
        ];

        $this->indexCategories(
            $user,
            $product->getCategories(),
            $productDocument
        );

        $this->indexTags(
            $product->getTags(),
            $productDocument
        );

        $manufacturer = $product->getManufacturer();
        if ($manufacturer instanceof Manufacturer) {
            $this->indexManufacturer(
                $user,
                $manufacturer,
                $productDocument
            );
        }

        $brand = $product->getBrand();
        if ($brand instanceof Brand) {
            $this->indexBrand(
                $user,
                $brand,
                $productDocument
            );
        }

        $document = new ElasticaDocument($productId, $productDocument);
        $document->setDocAsUpsert(true);

        $this
            ->elasticaWrapper
            ->getType(Product::TYPE)
            ->updateDocument($document);
    }

    /**
     * Index Categories and complete root Doc.
     *
     * @param string     $user
     * @param Category[] $categories
     * @param array      $rootDoc
     */
    private function indexCategories(
        string $user,
        array $categories,
        array &$rootDoc
    ) {
        $rootDoc['categories'] = [];
        foreach ($categories as $category) {
            $this->indexCategory(
                $user,
                $category,
                $rootDoc
            );
        }
    }

    /**
     * Index Category.
     *
     * @param string   $user
     * @param Category $category
     * @param array    $rootDoc
     */
    private function indexCategory(
        string $user,
        Category $category,
        array &$rootDoc
    ) {
        $categoryId = "$user~~{$category->getId()}";
        $document = new ElasticaDocument(
            $categoryId,
            [
                'user' => $user,
                'name' => $category->getName(),
                'level' => $category->getLevel(),
                'first_level_searchable_data' => $category->getFirstLevelSearchableData(),
            ]
        );
        $document->setDocAsUpsert(true);
        $this
            ->elasticaWrapper
            ->getType(Category::TYPE)
            ->updateDocument($document);

        $rootDoc['categories'][] = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'level' => $category->getLevel(),
        ];
    }

    /**
     * Index Tags and complete root Doc.
     *
     * @param string[] $tags
     * @param array    $rootDoc
     */
    private function indexTags(
        array $tags,
        array &$rootDoc
    ) {
        $rootDoc['tags'] = [];
        foreach ($tags as $tag) {
            $rootDoc['tags'][] = [
                'name' => $tag,
            ];
        }
    }

    /**
     * Index manufacturer.
     *
     * @param string       $user
     * @param Manufacturer $manufacturer
     * @param array        $rootDoc
     */
    private function indexManufacturer(
        string $user,
        Manufacturer $manufacturer,
        array &$rootDoc
    ) {
        $manufacturerId = "$user~~{$manufacturer->getId()}";
        $document = new ElasticaDocument(
            $manufacturerId,
            [
                'user' => $user,
                'name' => $manufacturer->getName(),
                'first_level_searchable_data' => $manufacturer->getFirstLevelSearchableData(),
            ]
        );
        $document->setDocAsUpsert(true);
        $this
            ->elasticaWrapper
            ->getType(Manufacturer::TYPE)
            ->updateDocument($document);

        $rootDoc['manufacturer'] = [
            'id' => $manufacturer->getId(),
            'name' => $manufacturer->getName(),
        ];
    }

    /**
     * Index brand.
     *
     * @param string $user
     * @param Brand  $brand
     * @param array  $rootDoc
     */
    private function indexBrand(
        string $user,
        Brand $brand,
        array &$rootDoc
    ) {
        $brandId = "$user~~{$brand->getId()}";
        $document = new ElasticaDocument(
            $brandId,
            [
                'user' => $user,
                'name' => $brand->getName(),
                'first_level_searchable_data' => $brand->getFirstLevelSearchableData(),
            ]
        );
        $document->setDocAsUpsert(true);
        $this
            ->elasticaWrapper
            ->getType(Brand::TYPE)
            ->updateDocument($document);

        $rootDoc['brand'] = [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
        ];
    }

    /**
     * Build a Result object given elastica result object.
     *
     * @param Query            $query
     * @param string           $user
     * @param ElasticaResult[] $elasticaResults
     *
     * @return Result
     */
    private function elasticaResultToResult(
        Query $query,
        string $user,
        array $elasticaResults
    ) : Result {
        $result = new Result();

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
            }
        }

        $resultAggregations = $elasticaResults['aggregations']['all']['all_products'];
        $aggregations = new ResultAggregations($resultAggregations['doc_count']);
        unset($resultAggregations['doc_count']);

        foreach ($resultAggregations as $aggregationName => $resultAggregation) {
            $queryAggregation = $query->getAggregation($aggregationName);
            $aggregation = new ResultAggregation(
                $queryAggregation->getType(),
                $resultAggregation['doc_count']
            );
            $aggregations->addAggregation($aggregationName, $aggregation);
            $buckets = isset($resultAggregation[$aggregationName]['buckets'])
                ? $resultAggregation[$aggregationName]['buckets']
                : $resultAggregation[$aggregationName][$aggregationName]['buckets'];

            if (empty($buckets)) {
                continue;
            }

            $relatedFilter = $query->getFilter($aggregationName);

            foreach ($buckets as $bucket) {
                $aggregation->addCounter(
                    $bucket['key'],
                    $bucket['doc_count'],
                    $relatedFilter->getValues()
                );
            }

            /**
             * We should filter the bucket elements with level that are not part
             * of the result.
             *
             * * Filter type MUST_ALL
             * * Elements already filtered
             * * Elements with level (if exists) than the highest one
             */
            if ($relatedFilter->getApplicationType() === Filter::MUST_ALL) {
                $aggregation->cleanCountersByLevel();
            }
        }
        $result->setAggregations($aggregations);

        return $result;
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter[]                $filters
     * @param string                  $filterToIgnore
     */
    private function addFilters(
        ElasticaQuery\BoolQuery $boolQuery,
        array $filters,
        string $filterToIgnore = null
    ) {
        foreach ($filters as $filterName => $filter) {
            $onlyAddDefinedTermFilter = (
                empty($filter->getValues()) ||
                $filterName === $filterToIgnore ||
                $filterName === "tags.$filterToIgnore"
            );

            $this->addFilter(
                $boolQuery,
                $filter,
                $onlyAddDefinedTermFilter
            );
        }
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter                  $filter
     * @param bool                    $onlyAddDefinedTermFilter
     */
    private function addFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter,
        bool $onlyAddDefinedTermFilter
    ) {
        if ($filter->getFilterType() === Filter::TYPE_RANGE) {
            $boolQuery->addFilter(
                $this->createPriceRangeFilter($filter)
            );

            return;
        }

        if ($filter->getFilterType() === Filter::TYPE_QUERY) {
            $boolQuery->addMust(
                $filter->getField() === Query::MATCH_ALL
                    ? new ElasticaQuery\MatchAll()
                    : new ElasticaQuery\Match('_all', $filter->getField())
            );

            return;
        }

        $boolQuery->addFilter(
            $filter->getApplicationType() === Filter::MUST_ALL
                ? $this->createQueryFilterMustAll($filter, $onlyAddDefinedTermFilter)
                : $this->createQueryFilterAtLeastOne($filter, $onlyAddDefinedTermFilter)
        );
    }

    /**
     * Creates a filter where all elements must match.
     *
     * @param Filter $filter
     * @param bool   $onlyAddDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterMustAll(
        Filter $filter,
        bool $onlyAddDefinedTermFilter
    ) : ElasticaQuery\AbstractQuery {
        $boolQueryFilter = new ElasticaQuery\BoolQuery();
        foreach ($filter->getValues() as $value) {
            $queryFilter = $this->createQueryFilter(
                $filter,
                (string) $value,
                $onlyAddDefinedTermFilter
            );

            if ($queryFilter instanceof ElasticaQuery\AbstractQuery) {
                $boolQueryFilter->addMust($queryFilter);
            }
        }

        return $boolQueryFilter;
    }

    /**
     * Creates a filter where, at least, one element should match.
     *
     * @param Filter $filter
     * @param bool   $onlyAddDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterAtLeastOne(
        Filter $filter,
        bool $onlyAddDefinedTermFilter
    ) : ElasticaQuery\AbstractQuery {
        $boolQueryFilter = new ElasticaQuery\BoolQuery();
        foreach ($filter->getValues() as $value) {
            $queryFilter = $this->createQueryFilter(
                $filter,
                (string) $value,
                $onlyAddDefinedTermFilter
            );

            if ($queryFilter instanceof ElasticaQuery\AbstractQuery) {
                $boolQueryFilter->addShould($queryFilter);
            }
        }

        return $boolQueryFilter;
    }

    /**
     * Creates Term/Terms query depending on the elements value.
     *
     * @param Filter $filter
     * @param string $value
     * @param bool   $onlyAddDefinedTermFilter
     *
     * @return null|ElasticaQuery\AbstractQuery
     */
    private function createQueryFilter(
        Filter $filter,
        string $value,
        bool $onlyAddDefinedTermFilter
    ) : ? ElasticaQuery\AbstractQuery {
        return $filter->getFilterType() === Filter::TYPE_NESTED
            ? $this->createdNestedTermFilter(
                $filter,
                $value,
                $onlyAddDefinedTermFilter
            )
            : $this->createTermFilter(
                $filter,
                $value,
                $onlyAddDefinedTermFilter
            );
    }

    /**
     * Adds terms filter given a BoolQuery.
     *
     * @param Filter $filter
     * @param string $value
     * @param bool   $onlyAddDefinedTermFilter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createdNestedTermFilter(
        Filter $filter,
        string $value,
        bool $onlyAddDefinedTermFilter
    ) : ElasticaQuery\AbstractQuery {
        list($path, $fieldName) = explode('.', $filter->getField(), 2);

        $nestedQuery = new ElasticaQuery\Nested();
        $nestedQuery->setPath($path);
        $nestedQuery->setScoreMode('max');
        $queryFilter = $this->createTermFilter(
            $filter,
            $value,
            $onlyAddDefinedTermFilter
        );

        if ($queryFilter instanceof ElasticaQuery\AbstractQuery) {
            $nestedQuery->setQuery($queryFilter);
        }

        return $nestedQuery;
    }

    /**
     * Create and return Term filter
     * Returns null if no need to be applicable (true=true).
     *
     * @param Filter $filter
     * @param string $value
     * @param bool   $onlyAddDefinedTermFilter
     *
     * @return null|ElasticaQuery\AbstractQuery
     */
    private function createTermFilter(
        Filter $filter,
        string $value,
        bool $onlyAddDefinedTermFilter
    ) : ? ElasticaQuery\AbstractQuery {
        $terms = $filter->getFilterTerms();
        if (!$onlyAddDefinedTermFilter) {
            $terms = array_merge(
                $terms,
                [
                    $filter->getField() => $value,
                ]
            );
        }
        if (empty($terms)) {
            return null;
        }

        return count($terms) > 1
            ? $this->createMultipleTermFilter($terms)
            : new ElasticaQuery\Term($terms);
    }

    /**
     * Create multiple Term filter.
     *
     * @param array $terms
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createMultipleTermFilter(array $terms) : ElasticaQuery\AbstractQuery
    {
        $boolQuery = new ElasticaQuery\BoolQuery();
        foreach ($terms as $termName => $termValue) {
            $boolQuery->addMust(
                new ElasticaQuery\Term([$termName => $termValue])
            );
        }

        return $boolQuery;
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
     * Set aggregations.
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
                $aggregation->getType() !== Filter::MUST_ALL
                    ? $aggregation->getName()
                    : null
            );

            $filteredAggregation->setFilter($boolQuery);
            $filteredAggregation->addAggregation($elasticaAggregation);
            $productsAggregation->addAggregation($filteredAggregation);
        }

        $elasticaQuery->addAggregation($globalAggregation);
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
