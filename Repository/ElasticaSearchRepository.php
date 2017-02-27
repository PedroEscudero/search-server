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

use Elastica\Document as ElasticaDocument;
use Elastica\Query as ElasticaQuery;
use Elastica\Result as ElasticaResult;

use Mmoreram\SearchBundle\Elastica\ElasticaWrapper;
use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Model;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Result;
use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\SortBy;

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

        $boolQuery->addMust(
            $query->getQueryText() === Query::MATCH_ALL
                ? new ElasticaQuery\MatchAll()
                : new ElasticaQuery\Match('_all', $query->getQueryText())
        );

        $this->addFilters($boolQuery, $query->getFilters());

        if (!is_null($query->getPriceRange())) {
            $priceRange = $query->getPriceRange();
            $priceRangeData = [
                'gte' => $priceRange->getFrom(),
            ];

            if ($priceRange->getTo() !== PriceRange::INFINITE) {
                $priceRangeData['lte'] = $priceRange->getTo();
            }

            $boolQuery->addFilter(
                new ElasticaQuery\Range('real_price', $priceRangeData)
            );
        }

        $mainQuery->setQuery($boolQuery);
        $mainQuery->setSort(
            $this->addSortBys($query->getSorts())
        );

        $results = $this
            ->elasticaWrapper
            ->search(
                $mainQuery,
                $query->getFrom(),
                $query->getSize()
            );

        return $this->elasticaResultToResult(
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
            ->getType(Model::PRODUCT)
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
                'first_level_searchable_data' => $category->getFirstLevelSearchableData(),
            ]
        );
        $document->setDocAsUpsert(true);
        $this
            ->elasticaWrapper
            ->getType(Model::CATEGORY)
            ->updateDocument($document);

        $rootDoc['categories'][] = [
            'id' => $category->getId(),
            'name' => $category->getName(),
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
            ->getType(Model::MANUFACTURER)
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
            ->getType(Model::BRAND)
            ->updateDocument($document);

        $rootDoc['brand'] = [
            'id' => $brand->getId(),
            'name' => $brand->getName(),
        ];
    }

    /**
     * Build a Result object given elastica result object.
     *
     * @param string           $user
     * @param ElasticaResult[] $elasticaResults
     *
     * @return Result
     */
    private function elasticaResultToResult(
        string $user,
        array $elasticaResults
    ) : Result {
        $result = new Result();

        foreach ($elasticaResults as $elasticaResult) {
            $source = $elasticaResult->getSource();
            $source['id'] = str_replace("$user~~", '', $elasticaResult->getId());
            switch ($elasticaResult->getType()) {
                case Model::PRODUCT:
                    $result->addProduct(
                        Product::createFromArray($source)
                    );
                    break;
                case Model::CATEGORY:
                    $result->addCategory(
                        Category::createFromArray($source)
                    );
                    break;
                case Model::MANUFACTURER:
                    $result->addManufacturer(
                        Manufacturer::createFromArray($source)
                    );
                    break;
                case Model::BRAND:
                    $result->addBrand(
                        Brand::createFromArray($source)
                    );
                    break;
            }
        }

        return $result;
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter[]                $filters
     */
    private function addFilters(
        ElasticaQuery\BoolQuery $boolQuery,
        array $filters
    ) {
        foreach ($filters as $filter) {
            if (empty($filter->getValues())) {
                continue;
            }

            $this->addFilter(
                $boolQuery,
                $filter
            );
        }
    }

    /**
     * Add filters to a Query.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param Filter                  $filter
     */
    private function addFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter
    ) {
        $filter->isNested()
            ? $this->addNestedTermsFilter(
                $boolQuery,
                $filter
            )
            : $this->addTermsFilter(
                $boolQuery,
                $filter
            );
    }

    /**
     * Filters by terms only if the field exists and the terms what to look for
     * are not just an empty array.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery,
     * @param Filter                  $filter
     */
    private function addTermsFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter
    ) {
        $boolQuery->addFilter(
            $this->createQueryFilter($filter)
        );
    }

    /**
     * Adds terms filter given a BoolQuery.
     *
     * @param ElasticaQuery\BoolQuery $boolQuery,
     * @param Filter                  $filter
     */
    private function addNestedTermsFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        Filter $filter
    ) {
        list($path, $fieldName) = explode('.', $filter->getField(), 2);

        $nestedQuery = new ElasticaQuery\Nested();
        $nestedQuery->setPath($path);
        $nestedQuery->setScoreMode('max');
        $nestedQuery->setQuery(
            $this->createQueryFilter($filter)
        );
        $boolQuery->addFilter($nestedQuery);
    }

    /**
     * Creates Term/Terms query depending on the elements value.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilter(Filter $filter) : ElasticaQuery\AbstractQuery
    {
        return $filter->getType() === Filter::MUST_ALL
            ? $this->createQueryFilterMustAll($filter)
            : $this->createQueryFilterAtLeastOne($filter);
    }

    /**
     * Creates a filter where all elements must match.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterMustAll(Filter $filter) : ElasticaQuery\AbstractQuery
    {
        $queryFilter = new ElasticaQuery\BoolQuery();
        foreach ($filter->getValues() as $value) {
            $queryFilter->addMust(
                new ElasticaQuery\Term([
                    $filter->getField() => (string) $value,
                ])
            );
        }

        return $queryFilter;
    }

    /**
     * Creates a filter where, at least, one element should match.
     *
     * @param Filter $filter
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createQueryFilterAtLeastOne(Filter $filter) : ElasticaQuery\AbstractQuery
    {
        $queryFilter = new ElasticaQuery\BoolQuery();
        foreach ($filter->getValues() as $value) {
            $queryFilter->addShould(
                new ElasticaQuery\Term([
                    $filter->getField() => (string) $value,
                ])
            );
        }

        return $queryFilter;
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
}
