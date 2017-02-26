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

namespace Mmoreram\SearchBundle\Model;

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
}
