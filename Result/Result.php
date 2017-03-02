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

namespace Mmoreram\SearchBundle\Result;

use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Tag;

/**
 * Class Result.
 */
class Result
{
    /**
     * @var Product[]
     *
     * Products
     */
    private $products = [];

    /**
     * @var Category[]
     *
     * Categories
     */
    private $categories = [];

    /**
     * @var Manufacturer[]
     *
     * Manufacturers
     */
    private $manufacturers = [];

    /**
     * @var Brand[]
     *
     * Brands
     */
    private $brands = [];

    /**
     * @var Tag[]
     *
     * Tags
     */
    private $tags = [];

    /**
     * @var Aggregations
     *
     * Aggregations
     */
    private $aggregations;

    /**
     * Total elements.
     *
     * @var int
     */
    private $totalElements;

    /**
     * Total products.
     *
     * @var int
     */
    private $totalProducts;

    /**
     * Total hits.
     *
     * @var int
     */
    private $totalHits;

    /**
     * Min price.
     *
     * @var int
     */
    private $minPrice;

    /**
     * Max price.
     *
     * @var int
     */
    private $maxPrice;

    /**
     * Result constructor.
     *
     * @param int $totalElements
     * @param int $totalProducts
     * @param int $totalHits
     * @param int $minPrice
     * @param int $maxPrice
     */
    public function __construct(
        int $totalElements,
        int $totalProducts,
        int $totalHits,
        int $minPrice,
        int $maxPrice
    ) {
        $this->totalElements = $totalElements;
        $this->totalProducts = $totalProducts;
        $this->totalHits = $totalHits;
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
    }

    /**
     * Add product.
     *
     * @param Product $product
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;
    }

    /**
     * Get products.
     *
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * Add category.
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
    }

    /**
     * Get categories.
     *
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Add manufacturer.
     *
     * @param Manufacturer $manufacturer
     */
    public function addManufacturer(Manufacturer $manufacturer)
    {
        $this->manufacturers[] = $manufacturer;
    }

    /**
     * Get manufacturers.
     *
     * @return Manufacturer[]
     */
    public function getManufacturers(): array
    {
        return $this->manufacturers;
    }

    /**
     * Add brand.
     *
     * @param Brand $brand
     */
    public function addBrand(Brand $brand)
    {
        $this->brands[] = $brand;
    }

    /**
     * Get brands.
     *
     * @return Brand[]
     */
    public function getBrands(): array
    {
        return $this->brands;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    /**
     * Get tags.
     *
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Set aggregations.
     *
     * @param Aggregations $aggregations
     */
    public function setAggregations(Aggregations $aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get aggregations.
     *
     * @return Aggregations
     */
    public function getAggregations(): Aggregations
    {
        return $this->aggregations;
    }

    /**
     * Get aggregation.
     *
     * @param string $name
     *
     * @return null|Aggregation
     */
    public function getAggregation(string $name) : ? Aggregation
    {
        return $this
            ->getAggregations()
            ->getAggregation($name);
    }

    /**
     * Total elements.
     *
     * @return int
     */
    public function getTotalElements() : int
    {
        return $this->totalElements;
    }

    /**
     * Total products.
     *
     * @return int
     */
    public function getTotalProducts(): int
    {
        return $this->totalProducts;
    }

    /**
     * Get total hits.
     *
     * @return int
     */
    public function getTotalHits() : int
    {
        return $this->totalHits;
    }

    /**
     * Get min price.
     *
     * @return int
     */
    public function getMinPrice(): int
    {
        return $this->minPrice;
    }

    /**
     * Get max price.
     *
     * @return int
     */
    public function getMaxPrice(): int
    {
        return $this->maxPrice;
    }
}
