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
use Mmoreram\SearchBundle\Query\Query;

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
     * @param Query $query
     *
     * @return Result
     */
    public function search(
        string $user,
        Query $query
    ) : Result {
        $boolQuery = new ElasticaQuery\BoolQuery();

        $boolQuery->addFilter(
            new ElasticaQuery\Term(['user' => $user])
        );

        $boolQuery->addMust(
            new ElasticaQuery\Match('_all', $query->getQueryText())
        );

        $this->addTermsFilter($boolQuery, 'family', $query->getFamilies());
        $this->addNestedTermsFilter($boolQuery, 'category', 'id', $query->getCategories());
        $this->addTermsFilter($boolQuery, 'manufacturer.id', $query->getManufacturer());
        $this->addTermsFilter($boolQuery, 'brand.id', $query->getBrand());


        if (!empty($query->getTypes())) {
            $boolQuery->addFilter(
                new ElasticaQuery\Terms('type', $query->getTypes())
            );
        }

        if (!is_null($query->getPriceRange())) {
            $boolQuery->addFilter(
                new ElasticaQuery\Range('real_price', [
                    'gte' => $query->getPriceRange()->getFrom(),
                    'lte' => $query->getPriceRange()->getFrom()
                ])
            );
        }

        $results = $this
            ->elasticaWrapper
            ->search($boolQuery);

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
            'description' => $product->getDescription(),
            'long_description' => $product->getLongDescription(),
            'price' => $product->getPrice(),
            'reduced_price' => $product->getReducedPrice(),
            'real_price' => $product->getRealPrice(),
            'first_level_searchable_data' => $product->getFirstLevelSearchableData(),
            'second_level_searchable_data' => $product->getSecondLevelSearchableData(),
        ];

        $this->indexCategories(
            $user,
            $product->getCategories(),
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
        $rootDoc['category'] = [];
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

        $rootDoc['category'][] = [
            'id' => $category->getId(),
            'name' => $category->getName(),
        ];
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
                case 'product':
                    $result->addProduct(
                        Product::createFromArray($source)
                    );
                    break;
                case 'category':
                    $result->addCategory(
                        Category::createFromArray($source)
                    );
                    break;
                case 'manufacturer':
                    $result->addManufacturer(
                        Manufacturer::createFromArray($source)
                    );
                    break;
                case 'brand':
                    $result->addBrand(
                        Brand::createFromArray($source)
                    );
                    break;
            }
        }

        return $result;
    }

    /**
     * Filters by terms only if the field exists and the terms what to look for
     * are not just an empty array
     *
     * @param ElasticaQuery\BoolQuery $boolQuery,
     * @param string $fieldName
     * @param null|string|array $elements
     */
    private function addTermsFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        string $fieldName,
        $elements
    )
    {
        if (!empty($elements)) {

            $boolQuery->addFilter(
                $this->createTermsFilterDependingOnElement(
                    "$fieldName",
                    $elements
                )
            );
        }
    }

    /**
     * Adds terms filter given a BoolQuery
     *
     * @param ElasticaQuery\BoolQuery $boolQuery,
     * @param string $path
     * @param string $fieldName
     * @param null|string|array $elements
     */
    private function addNestedTermsFilter(
        ElasticaQuery\BoolQuery $boolQuery,
        string $path,
        string $fieldName,
        $elements
    )
    {
        if (!empty($elements)) {
            $nestedQuery = new ElasticaQuery\Nested();
            $nestedQuery->setPath($path);
            $nestedQuery->setScoreMode('max');
            $nestedQuery->setQuery(
                $this->createTermsFilterDependingOnElement(
                    "$path.$fieldName",
                    $elements
                )
            );
            $boolQuery->addFilter($nestedQuery);
        }
    }

    /**
     * Creates Term/Terms query depending on the elements value
     *
     * @param string $fieldName
     * @param null|string|array $elements
     *
     * @return ElasticaQuery\AbstractQuery
     */
    private function createTermsFilterDependingOnElement(
        string $fieldName,
        $elements
    ) : ElasticaQuery\AbstractQuery
    {
        return is_array($elements)
            ? new ElasticaQuery\Terms($fieldName, $elements)
            : new ElasticaQuery\Term([$fieldName, (string) $elements]);
    }
}
