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
     * @var array
     *
     * Documents
     */
    private $documents;

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
            ->refresh();
        $this->resetDocumentCache();
    }

    /**
     * Generate product document.
     *
     * @param string  $user
     * @param Product $product
     *
     * @return ElasticaDocument
     */
    public function addProduct(
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

        $this->addCategories(
            $user,
            $product->getCategories(),
            $productDocument
        );

        $this->addTags(
            $user,
            $product->getTags(),
            $productDocument
        );

        $manufacturer = $product->getManufacturer();
        if ($manufacturer instanceof Manufacturer) {
            $this->addManufacturer(
                $user,
                $manufacturer,
                $productDocument
            );
        }

        $brand = $product->getBrand();
        if ($brand instanceof Brand) {
            $this->addBrand(
                $user,
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
     * @param string     $user
     * @param Category[] $categories
     * @param array      $rootDoc
     */
    private function addCategories(
        string $user,
        array $categories,
        array &$rootDoc
    ) {
        $rootDoc['categories'] = [];
        foreach ($categories as $category) {
            $this->addCategory(
                $user,
                $category,
                $rootDoc
            );
        }
    }

    /**
     * Add Category.
     *
     * @param string   $user
     * @param Category $category
     * @param array    $rootDoc
     */
    public function addCategory(
        string $user,
        Category $category,
        array &$rootDoc = null
    ) {
        $categoryId = "$user~~{$category->getId()}";

        if (!isset($this->documents['categories'][$categoryId])) {
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
     * @param string       $user
     * @param Manufacturer $manufacturer
     * @param array        $rootDoc
     */
    private function addManufacturer(
        string $user,
        Manufacturer $manufacturer,
        array &$rootDoc = null
    ) {
        $manufacturerId = "$user~~{$manufacturer->getId()}";

        if (!isset($this->documents['manufacturers'][$manufacturerId])) {
            $document = new ElasticaDocument(
                $manufacturerId,
                [
                    'user' => $user,
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
     * @param string $user
     * @param Brand  $brand
     * @param array  $rootDoc
     */
    private function addBrand(
        string $user,
        Brand $brand,
        array &$rootDoc = null
    ) {
        $brandId = "$user~~{$brand->getId()}";

        if (!isset($this->documents['brands'][$brandId])) {
            $document = new ElasticaDocument(
                $brandId,
                [
                    'user' => $user,
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
     * @param string $user
     * @param Tag[]  $tags
     * @param array  $rootDoc
     */
    private function addTags(
        string $user,
        array $tags,
        array &$rootDoc = null
    ) {
        $rootDoc['tags'] = [];
        foreach ($tags as $tag) {
            $this->addTag(
                $user,
                $tag,
                $rootDoc
            );
        }
    }

    /**
     * Add tag.
     *
     * @param string $user
     * @param Tag    $tag
     * @param array  $rootDoc
     */
    private function addTag(
        string $user,
        Tag $tag,
        array &$rootDoc = null
    ) {
        $tagId = "$user~~{$tag->getName()}";

        if (!isset($this->documents['tags'][$tagId])) {
            $document = new ElasticaDocument(
                $tagId,
                [
                    'user' => $user,
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
        $this->elasticaWrapper->getType(Manufacturer::TYPE)->updateDocuments($this->documents['manufacturers']);
        $this->elasticaWrapper->getType(Category::TYPE)->updateDocuments($this->documents['categories']);
        $this->elasticaWrapper->getType(Brand::TYPE)->updateDocuments($this->documents['brands']);
        $this->elasticaWrapper->getType(Tag::TYPE)->updateDocuments($this->documents['tags']);
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
                ->getType(Product::TYPE)
                ->updateDocuments($documents);
            $offset += $bulkNumber;
        }
    }
}
