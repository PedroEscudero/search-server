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

namespace Mmoreram\SearchBundle\Query;

/**
 * Class Query.
 */
class Query
{
    /**
     * @var string
     *
     * Match all
     */
    const MATCH_ALL = '*';

    /**
     * @var string
     *
     * Query text
     */
    private $queryText;

    /**
     * @var Filter[]
     *
     * Filters
     */
    private $filters = [];

    /**
     * @var PriceRange
     *
     * Price range
     */
    private $priceRange;

    /**
     * @var string[]
     *
     * Sorts
     */
    private $sorts = [];

    /**
     * @var int
     *
     * From
     */
    private $from;

    /**
     * @var int
     *
     * Size
     */
    private $size;

    /**
     * Construct.
     *
     * @param string $queryText
     */
    private function __construct(string $queryText)
    {
        $this->queryText = $queryText;
    }

    /**
     * Create new.
     *
     * @param string $queryText
     * @param int    $from
     * @param int    $size
     *
     * @return self
     */
    public static function create(
        string $queryText,
        int $from = 0,
        int $size = 10
    ) : self {
        $query = new self($queryText);
        $query->from = $from;
        $query->size = $size;

        return $query;
    }

    /**
     * Create new query all.
     *
     * @return self
     */
    public static function createMatchAll()
    {
        $query = new self(self::MATCH_ALL);
        $query->from = 0;
        $query->size = 1000;

        return $query;
    }

    /**
     * Filter by families.
     *
     * @param array  $families
     * @param string $filterType
     *
     * @return self
     */
    public function filterByFamilies(
        array $families,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['families'] = Filter::create(
            'family',
            $families,
            $filterType,
            false
        );

        return $this;
    }

    /**
     * Filter by types.
     *
     * @param array  $types
     * @param string $filterType
     *
     * @return self
     */
    public function filterByTypes(
        array $types,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['types'] = Filter::create(
            'type',
            $types,
            $filterType,
            false
        );

        return $this;
    }

    /**
     * Filter by categories.
     *
     * @param array  $categories
     * @param string $filterType
     *
     * @return self
     */
    public function filterByCategories(
        array $categories,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['categories'] = Filter::create(
            'categories.id',
            $categories,
            $filterType,
            true
        );

        return $this;
    }

    /**
     * Filter by manufacturer.
     *
     * @param array  $manufacturers
     * @param string $filterType
     *
     * @return self
     */
    public function filterByManufacturers(
        array $manufacturers,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['manufacturers'] = Filter::create(
            'manufacturer.id',
            $manufacturers,
            $filterType,
            false
        );

        return $this;
    }

    /**
     * Filter by brand.
     *
     * @param array  $brands
     * @param string $filterType
     *
     * @return self
     */
    public function filterByBrands(
        array $brands,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['brands'] = Filter::create(
            'brand.id',
            $brands,
            $filterType,
            false
        );

        return $this;
    }

    /**
     * Filter by tag.
     *
     * @param array  $tags
     * @param string $filterType
     *
     * @return self
     */
    public function filterByTags(
        array $tags,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->filters['tags'] = Filter::create(
            'tag.name',
            $tags,
            $filterType,
            false
        );

        return $this;
    }

    /**
     * Filter by price range.
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
     * Remove filter by price range.
     *
     * @return self
     */
    public function removeFilterByPriceRange() : self
    {
        $this->priceRange = null;

        return $this;
    }

    /**
     * Sort by.
     *
     * @param array|string $string
     *
     * @return self
     */
    public function sortBy($string)
    {
        $this->sorts[] = $string;

        return $this;
    }

    /**
     * Remove sorts.
     *
     * @return self
     */
    public function removeSorts() : self
    {
        $this->sorts = null;

        return $this;
    }

    /**
     * Return Querytext.
     *
     * @return string
     */
    public function getQueryText() : string
    {
        return $this->queryText;
    }

    /**
     * Get filters.
     *
     * @return Filter[]
     */
    public function getFilters() : array
    {
        return $this->filters;
    }

    /**
     * Get price range.
     *
     * @return null|PriceRange
     */
    public function getPriceRange() : ? PriceRange
    {
        return $this->priceRange;
    }

    /**
     * Get sorts.
     *
     * @return array $sorts
     */
    public function getSorts() : array
    {
        return $this->sorts;
    }

    /**
     * Get from.
     *
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
