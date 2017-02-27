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
     * @var Filter[]
     *
     * Tag Filters
     */
    private $tagFilters = [];

    /**
     * @var string[]
     *
     * Sorts
     */
    private $sorts = [];

    /**
     * @var string[]
     *
     * Aggregations
     */
    private $aggregations = [];

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
        $this->filters['family'] = Filter::create(
            'family',
            $families,
            $filterType,
            Filter::TYPE_FIELD
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
        $this->filters['type'] = Filter::create(
            '_type',
            $types,
            $filterType,
            Filter::TYPE_FIELD
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
            Filter::TYPE_NESTED
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
        $this->filters['manufacturer'] = Filter::create(
            'manufacturer.id',
            $manufacturers,
            $filterType,
            Filter::TYPE_FIELD
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
        $this->filters['brand'] = Filter::create(
            'brand.id',
            $brands,
            $filterType,
            Filter::TYPE_FIELD
        );

        return $this;
    }

    /**
     * Filter by tags.
     *
     * @param string $groupName
     * @param array  $tags
     * @param string $filterType
     *
     * @return self
     */
    public function filterByTags(
        string $groupName,
        array $tags,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->tagFilters["tags.$groupName"] = Filter::create(
            'tags.name',
            $tags,
            $filterType,
            Filter::TYPE_NESTED
        );

        return $this;
    }

    /**
     * Filter by tag.
     *
     * @param string $tag
     * @param string $filterType
     *
     * @return self
     */
    public function filterByTag(
        string $tag,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->tagFilters["tags.$tag"] = Filter::create(
            'tags.name',
            [$tag],
            $filterType,
            Filter::TYPE_NESTED
        );

        return $this;
    }

    /**
     * Remove all tag filters.
     *
     * @return self
     */
    public function removeAllTagFilters()
    {
        $this->tagFilters = [];

        return $this;
    }

    /**
     * Remove tag  by the tag name or the tag group name.
     *
     * @param string $tag
     *
     * @return self
     */
    public function removeTagFilter(string $tag) : self
    {
        unset($this->tagFilters["tags.$tag"]);

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
        $this->tagFilters['real_price'] = Filter::create(
            'real_price',
            [
                'from' => $from,
                'to' => $to,
            ],
            '',
            Filter::TYPE_RANGE
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
        unset($this->tagFilters['real_price']);

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
     * Add Manufacturer aggregation.
     *
     * @return self
     */
    public function addManufacturerAggregation()
    {
        $this->aggregations['manufacturer'] = Aggregation::create(
            'manufacturer',
            'manufacturer.name',
            false
        );

        return $this;
    }

    /**
     * Remove Manufacturer aggregation.
     *
     * @return self
     */
    public function removeManufacturerAggregation()
    {
        unset($this->aggregations['manufacturer']);

        return $this;
    }

    /**
     * Add Brand aggregation.
     *
     * @return self
     */
    public function addBrandAggregation()
    {
        $this->aggregations['brand'] = Aggregation::create(
            'brand',
            'brand.name',
            false
        );

        return $this;
    }

    /**
     * Remove Brand aggregation.
     *
     * @return self
     */
    public function removeBrandAggregation()
    {
        unset($this->aggregations['brand']);

        return $this;
    }

    /**
     * Add categories aggregation.
     *
     * @return self
     */
    public function addCategoriesAggregation()
    {
        $this->aggregations['categories'] = Aggregation::create(
            'categories',
            'categories.name',
            true
        );

        return $this;
    }

    /**
     * Remove categories aggregation.
     *
     * @return self
     */
    public function removeCategoriesAggregation()
    {
        unset($this->aggregations['categories']);

        return $this;
    }

    /**
     * Aggregate by.
     *
     * @param string $aggregation
     *
     * @return self
     */
    public function aggregateBy(string $aggregation)
    {
        $this->aggregations[] = $aggregation;

        return $this;
    }

    /**
     * Remove aggregations.
     *
     * @return self
     */
    public function removeAggregations()
    {
        $this->aggregations = [];

        return $this;
    }

    /**
     * Get aggregations.
     *
     * @return string[]
     */
    public function getAggregations()
    {
        return $this->aggregations;
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
        return array_merge(
            $this->filters,
            $this->tagFilters
        );
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
