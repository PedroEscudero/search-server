<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Mmoreram\SearchBundle\Query;

/**
 * Class Query
 */
class Query
{
    /**
     * @var string
     *
     * Query text
     */
    private $queryText;

    /**
     * @var string[]
     *
     * Categories
     */
    private $categories = [];

    /**
     * @var string[]
     *
     * Families
     */
    private $families = [];

    /**
     * @var string[]
     *
     * Types
     */
    private $types = [];

    /**
     * @var string
     *
     * Manufacturer
     */
    private $manufacturer;

    /**
     * @var string
     *
     * Brand
     */
    private $brand;

    /**
     * @var PriceRange
     *
     * Price range
     */
    private $priceRange;

    /**
     * Construct
     *
     * @param string $queryText
     */
    private function __construct(string $queryText)
    {
        $this->queryText = $queryText;
    }

    /**
     * Create new
     *
     * @param string $queryText
     *
     * @return self
     */
    public static function create(string $queryText) : self
    {
        return new self($queryText);
    }

    /**
     * Filter by families
     *
     * @param null|array $families
     *
     * @return self
     */
    public function filterByFamilies(?array $families) : self
    {
        $this->families = $families ?? [];

        return $this;
    }

    /**
     * Filter by types
     *
     * @param null|array $types
     *
     * @return self
     */
    public function filterByTypes(?array $types) : self
    {
        $this->types = $types ?? [];

        return $this;
    }

    /**
     * Filter by categories
     *
     * @param null|array $categories
     *
     * @return self
     */
    public function filterByCategories(?array $categories) : self
    {
        $this->categories = $categories ?? [];

        return $this;
    }

    /**
     * Filter by manufacturer
     *
     * @param null|string $manufacturer
     *
     * @return self
     */
    public function filterByManufacturer(?string $manufacturer) : self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Filter by brand
     *
     * @param null|string $brand
     *
     * @return self
     */
    public function filterByBrand(?string $brand) : self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Filter by price range
     *
     * @param int $from
     * @param int $to
     *
     * @return self
     */
    public function filterByPriceRange(
        int $from,
        int $to
    ) : self {
        $this->priceRange = new PriceRange(
            $from,
            $to
        );

        return $this;
    }

    /**
     * Remove filter by price range
     *
     * @return self
     */
    public function removeFilterByPriceRange() : self
    {
        $this->priceRange = null;

        return $this;
    }

    /**
     * Return Querytext
     *
     * @return string
     */
    public function getQueryText() : string
    {
        return $this->queryText;
    }

    /**
     * Get categories
     *
     * @return string[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Get families
     *
     * @return string[]
     */
    public function getFamilies(): array
    {
        return $this->families;
    }

    /**
     * Get types
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get manufacturer
     *
     * @return null|string
     */
    public function getManufacturer(): ? string
    {
        return $this->manufacturer;
    }

    /**
     * Get brand
     *
     * @return null|string
     */
    public function getBrand(): ? string
    {
        return $this->brand;
    }

    /**
     * Get price range
     *
     * @return null|PriceRange
     */
    public function getPriceRange(): ? PriceRange
    {
        return $this->priceRange;
    }
}