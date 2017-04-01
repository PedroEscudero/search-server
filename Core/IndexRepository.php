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

use Elastica\Document;
use Elastica\Document as ElasticaDocument;

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Coordinate;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Server\Elastica\ElasticaWrapper;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWithKeyWrapper
{
    /**
     * Create the index.
     */
    public function createIndex()
    {
        $this
            ->elasticaWrapper
            ->createIndexMapping(
                $this->key,
                4
            );
    }

    /**
     * Generate product documents.
     *
     * @param Product[] $products
     */
    public function addProducts(array $products)
    {
        $documents = [];
        foreach ($products as $product) {
            $documents[] = $this->createProductDocument($product);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, Product::TYPE)
            ->updateDocuments($documents);

        $this->refresh();
    }

    /**
     * Create product document.
     *
     * @param Product $product
     *
     * @return Document
     */
    private function createProductDocument(Product $product) : Document
    {
        $composedProductId = $product
            ->getProductReference()
            ->composeUUID();

        $productSuggest = [];
        $productDocument = [
            'id' => $product->getId(),
            'family' => $product->getFamily(),
            'ean' => $product->getEan(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'sortable_name' => $product->getName(),
            'description' => $product->getDescription(),
            'long_description' => $product->getLongDescription(),
            'price' => $product->getPrice(),
            'reduced_price' => $product->getReducedPrice(),
            'real_price' => $product->getRealPrice(),
            'discount' => $product->getDiscount(),
            'discount_percentage' => $product->getDiscountPercentage(),
            'currency' => $product->getCurrency(),
            'stock' => $product->getStock(),
            'image' => $product->getImage(),
            'with_image' => !empty($product->getImage()),
            'rating' => $product->getRating(),
            'updated_at' => $product->getUpdatedAt()->format(DATE_ATOM),
            'coordinate' => $product->getCoordinate() instanceof Coordinate
                ? $product
                    ->getCoordinate()
                    ->toArray()
                : null,
            'stores' => $product->getStores(),
            'metadata' => $product->getMetadata(),
            'categories' => [],
            'tags' => [],
            'first_level_searchable_data' => $product->getFirstLevelSearchableData(),
            'second_level_searchable_data' => $product->getSecondLevelSearchableData(),
            'suggest' => $product->getName(),
        ];

        $productSuggest[] = $product->getName();

        foreach ($product->getCategories() as $category) {
            $productDocument['categories'][] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'level' => $category->getLevel(),
            ];
            $productSuggest[] = $category->getName();
        }

        foreach ($product->getTags() as $tag) {
            $productDocument['tags'][] = [
                'name' => $tag->getName(),
            ];
            $productSuggest[] = $tag->getName();
        }

        foreach ($product->getManufacturers() as $manufacturer) {
            $productDocument['manufacturers'][] = [
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
                'slug' => $manufacturer->getSlug(),
            ];
            $productSuggest[] = $manufacturer->getName();
        }

        $brand = $product->getBrand();
        if ($brand instanceof Brand) {
            $productDocument['brand'] = [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'slug' => $brand->getSlug(),
            ];
            $productSuggest[] = $brand->getName();
        }

        $productDocument['suggest'] = $productSuggest;
        $document = new ElasticaDocument($composedProductId, $productDocument);
        $document->setDocAsUpsert(true);

        return $document;
    }

    /**
     * Add categories.
     *
     * @param Category[] $categories
     */
    public function addCategories(array $categories)
    {
        $documents = [];
        foreach ($categories as $category) {
            $documents[] = $this->createCategoryDocument($category);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, Category::TYPE)
            ->updateDocuments($documents);

        $this->refresh();
    }

    /**
     * Create category document.
     *
     * @param Category $category
     *
     * @return Document
     */
    private function createCategoryDocument(Category $category) : Document
    {
        $document = new ElasticaDocument(
            $category
                ->getCategoryReference()
                ->composeUUID(),
            [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'level' => $category->getLevel(),
                'first_level_searchable_data' => $category->getFirstLevelSearchableData(),
                'suggest' => $category->getName(),
            ]
        );
        $document->setDocAsUpsert(true);

        return $document;
    }

    /**
     * Add manufacturers.
     *
     * @param Manufacturer[] $manufacturers
     */
    public function addManufacturers(array $manufacturers)
    {
        $documents = [];
        foreach ($manufacturers as $manufacturer) {
            $documents[] = $this->createManufacturerDocument($manufacturer);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, Manufacturer::TYPE)
            ->updateDocuments($documents);

        $this->refresh();
    }

    /**
     * Index manufacturer.
     *
     * @param Manufacturer $manufacturer
     *
     * @return Document
     */
    private function createManufacturerDocument(Manufacturer $manufacturer) : Document
    {
        $document = new ElasticaDocument(
            $manufacturer
                ->getManufacturerReference()
                ->composeUUID(),
            [
                'id' => $manufacturer->getId(),
                'name' => $manufacturer->getName(),
                'slug' => $manufacturer->getSlug(),
                'first_level_searchable_data' => $manufacturer->getFirstLevelSearchableData(),
                'suggest' => $manufacturer->getName(),
            ]
        );
        $document->setDocAsUpsert(true);

        return $document;
    }

    /**
     * Add brands.
     *
     * @param Brand[] $brands
     */
    public function addBrands(array $brands)
    {
        $documents = [];
        foreach ($brands as $brand) {
            $documents[] = $this->createBrandDocument($brand);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, Brand::TYPE)
            ->updateDocuments($documents);

        $this->refresh();
    }

    /**
     * Index brand.
     *
     * @param Brand $brand
     *
     * @return Document
     */
    private function createBrandDocument(Brand $brand) : Document
    {
        $document = new ElasticaDocument(
            $brand
                ->getBrandReference()
                ->composeUUID(),
            [
                'id' => $brand->getId(),
                'name' => $brand->getName(),
                'slug' => $brand->getSlug(),
                'first_level_searchable_data' => $brand->getFirstLevelSearchableData(),
                'suggest' => $brand->getName(),
            ]
        );
        $document->setDocAsUpsert(true);

        return $document;
    }

    /**
     * Add tags.
     *
     * @param Tag[] $tags
     */
    public function addTags(array $tags)
    {
        $documents = [];
        foreach ($tags as $tag) {
            $documents[] = $this->createTagDocument($tag);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, Tag::TYPE)
            ->updateDocuments($documents);

        $this->refresh();
    }

    /**
     * Index tag.
     *
     * @param Tag $tag
     *
     * @return Document
     */
    private function createTagDocument(Tag $tag) : Document
    {
        $document = new ElasticaDocument(
            $tag->getName(),
            [
                'name' => $tag->getName(),
                'first_level_searchable_data' => $tag->getFirstLevelSearchableData(),
                'suggest' => $tag->getName(),
            ]
        );
        $document->setDocAsUpsert(true);

        return $document;
    }
}
