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

use Mmoreram\SearchBundle\Elastica\ElasticaWrapper;
use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Tag;

/**
 * Class Index.
 */
class Index
{
    /**
     * @var ElasticaWrapper
     *
     * Elastica wrapper
     */
    private $elasticaWrapper;

    /**
     * @var string
     *
     * Key
     */
    private $key;

    /**
     * @var array
     *
     * Documents
     */
    private $documents = [];

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper $elasticaWrapper
     */
    public function __construct(ElasticaWrapper $elasticaWrapper)
    {
        $this->elasticaWrapper = $elasticaWrapper;
        $this->resetDocumentCache();
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Reset documents cache.
     */
    private function resetDocumentCache()
    {
        $this->documents = [
            'products' => [],
            'categories' => [],
            'manufacturers' => [],
            'brands' => [],
        ];
    }

    /**
     * Flush.
     *
     * @param int $bulkNumber
     */
    public function flush(int $bulkNumber)
    {
        $this->flushMinors();
        $this->flushProjects($bulkNumber);
        $this
            ->elasticaWrapper
            ->refresh($this->key);
        $this->resetDocumentCache();
    }

    /**
     * Generate product document.
     *
     * @param Product $product
     *
     * @return ElasticaDocument
     */
    public function addProduct(Product $product)
    {
        $productId = $product->getId();
        $productDocument = [
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
            'stock' => $product->getStock(),
            'rating' => $product->getRating(),
            'first_level_searchable_data' => $product->getFirstLevelSearchableData(),
            'second_level_searchable_data' => $product->getSecondLevelSearchableData(),
        ];

        $this->addCategories(
            $product->getCategories(),
            $productDocument
        );

        $this->addTags(
            $product->getTags(),
            $productDocument
        );

        $manufacturer = $product->getManufacturer();
        if ($manufacturer instanceof Manufacturer) {
            $this->addManufacturer(
                $manufacturer,
                $productDocument
            );
        }

        $brand = $product->getBrand();
        if ($brand instanceof Brand) {
            $this->addBrand(
                $brand,
                $productDocument
            );
        }

        $document = new ElasticaDocument($productId, $productDocument);
        $document->setDocAsUpsert(true);

        if (!isset($this->documents['products'][$productId])) {
            $this->documents['products'][$productId] = $document;
        }
    }

    /**
     * Index Categories and complete root Doc.
     *
     * @param Category[] $categories
     * @param array      $rootDoc
     */
    private function addCategories(
        array $categories,
        array &$rootDoc
    ) {
        $rootDoc['categories'] = [];
        foreach ($categories as $category) {
            $this->addCategory(
                $category,
                $rootDoc
            );
        }
    }

    /**
     * Add Category.
     *
     * @param Category $category
     * @param array    $rootDoc
     */
    public function addCategory(
        Category $category,
        array &$rootDoc = null
    ) {
        $categoryId = $category->getId();

        if (!isset($this->documents['categories'][$categoryId])) {
            $document = new ElasticaDocument(
                $categoryId,
                [
                    'name' => $category->getName(),
                    'level' => $category->getLevel(),
                    'first_level_searchable_data' => $category->getFirstLevelSearchableData(),
                ]
            );
            $document->setDocAsUpsert(true);
            $this->documents['categories'][$categoryId] = $document;
        }

        if (is_array($rootDoc)) {
            $rootDoc['categories'][] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'level' => $category->getLevel(),
            ];
        }
    }

    /**
     * Index manufacturer.
     *
     * @param Manufacturer $manufacturer
     * @param array        $rootDoc
     */
    private function addManufacturer(
        Manufacturer $manufacturer,
        array &$rootDoc = null
    ) {
        $manufacturerId = $manufacturer->getId();

        if (!isset($this->documents['manufacturers'][$manufacturerId])) {
            $document = new ElasticaDocument(
                $manufacturerId,
                [
                    'name' => $manufacturer->getName(),
                    'first_level_searchable_data' => $manufacturer->getFirstLevelSearchableData(),
                ]
            );
            $document->setDocAsUpsert(true);
            $this->documents['manufacturers'][$manufacturerId] = $document;
        }

        if (is_array($rootDoc)) {
            $rootDoc['manufacturer'] = [
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
            ];
        }
    }

    /**
     * Index brand.
     *
     * @param Brand $brand
     * @param array $rootDoc
     */
    private function addBrand(
        Brand $brand,
        array &$rootDoc = null
    ) {
        $brandId = $brand->getId();

        if (!isset($this->documents['brands'][$brandId])) {
            $document = new ElasticaDocument(
                $brandId,
                [
                    'name' => $brand->getName(),
                    'first_level_searchable_data' => $brand->getFirstLevelSearchableData(),
                ]
            );
            $document->setDocAsUpsert(true);
            $this->documents['brands'][$brandId] = $document;
        }

        if (is_array($rootDoc)) {
            $rootDoc['brand'] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
            ];
        }
    }

    /**
     * Index Tags and complete root Doc.
     *
     * @param Tag[] $tags
     * @param array $rootDoc
     */
    private function addTags(
        array $tags,
        array &$rootDoc = null
    ) {
        $rootDoc['tags'] = [];
        foreach ($tags as $tag) {
            $this->addTag(
                $tag,
                $rootDoc
            );
        }
    }

    /**
     * Add tag.
     *
     * @param Tag   $tag
     * @param array $rootDoc
     */
    private function addTag(
        Tag $tag,
        array &$rootDoc = null
    ) {
        $tagId = $tag->getName();

        if (!isset($this->documents['tags'][$tagId])) {
            $document = new ElasticaDocument(
                $tagId,
                [
                    'name' => $tag->getName(),
                    'first_level_searchable_data' => $tag->getFirstLevelSearchableData(),
                ]
            );
            $document->setDocAsUpsert(true);
            $this->documents['tags'][$tagId] = $document;
        }

        if (is_array($rootDoc)) {
            $rootDoc['tags'][] = [
                'name' => $tag->getName(),
            ];
        }
    }

    /**
     * Flush minors.
     */
    private function flushMinors()
    {
        $this->elasticaWrapper->getType($this->key, Manufacturer::TYPE)->updateDocuments($this->documents['manufacturers']);
        $this->elasticaWrapper->getType($this->key, Category::TYPE)->updateDocuments($this->documents['categories']);
        $this->elasticaWrapper->getType($this->key, Brand::TYPE)->updateDocuments($this->documents['brands']);
        $this->elasticaWrapper->getType($this->key, Tag::TYPE)->updateDocuments($this->documents['tags']);
    }

    /**
     * Flush projects.
     *
     * @param int $bulkNumber
     */
    private function flushProjects(int $bulkNumber)
    {
        $offset = 0;
        while (true) {
            $documents = array_slice(
                $this->documents['products'],
                $offset,
                $bulkNumber
            );

            if (empty($documents)) {
                return;
            }

            $this
                ->elasticaWrapper
                ->getType($this->key, Product::TYPE)
                ->updateDocuments($documents);
            $offset += $bulkNumber;
        }
    }
}
