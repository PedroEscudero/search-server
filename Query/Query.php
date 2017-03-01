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
     * @var Aggregation[]
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
        $this->filters['_query'] = Filter::create(
            $queryText,
            ['_'],
            self::MATCH_ALL,
            Filter::TYPE_QUERY
        );
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
        $queryText = empty($queryText)
            ? self::MATCH_ALL
            : $queryText;

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

        $this->addCategoriesAggregation($filterType);

        return $this;
    }

    /**
     * Remove categories filter.
     *
     * @return self
     */
    public function removeCategoriesFilter() : self
    {
        unset($this->filters['categories']);
        $this->removeCategoriesAggregation();

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

        $this->addManufacturerAggregation($filterType);

        return $this;
    }

    /**
     * Remove manufacturers filter.
     *
     * @return self
     */
    public function removeManufacturersFilter() : self
    {
        unset($this->filters['manufacturer']);
        $this->removeManufacturerAggregation();

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

        $this->addBrandAggregation($filterType);

        return $this;
    }

    /**
     * Remove brands filter.
     *
     * @return self
     */
    public function removeBrandsFilter() : self
    {
        unset($this->filters['brand']);
        $this->removeBrandAggregation();

        return $this;
    }

    /**
     * Filter by tags.
     *
     * @param string $groupName
     * @param array  $options
     * @param array  $tags
     * @param string $filterType
     *
     * @return self
     */
    public function filterByTags(
        string $groupName,
        array $options,
        array $tags,
        string $filterType = Filter::MUST_ALL
    ) : self {
        $this->tagFilters[$groupName] = Filter::create(
            'tags.name',
            $tags,
            $filterType,
            Filter::TYPE_NESTED,
            [
                'tags.name',
                $options,
            ]
        );

        $this->addTagsAggregation($groupName, $options, $filterType);

        return $this;
    }

    /**
     * Remove tag filter.
     *
     * @param string $groupName
     *
     * @return self
     */
    public function removeTagFilter(string $groupName)
    {
        unset($this->tagFilters[$groupName]);
        $this->removeTagsAggregation($groupName);

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
    public function sortBy($string) : self
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
     * @param string $type
     *
     * @return self
     */
    private function addManufacturerAggregation(string $type = Filter::MUST_ALL) : self
    {
        $this->aggregations['manufacturer'] = Aggregation::create(
            'manufacturer',
            'manufacturer.id|manufacturer.name',
            $type,
            false
        );

        return $this;
    }

    /**
     * Remove Manufacturer aggregation.
     *
     * @return self
     */
    private function removeManufacturerAggregation() : self
    {
        unset($this->aggregations['manufacturer']);

        return $this;
    }

    /**
     * Add Brand aggregation.
     *
     * @param string $type
     *
     * @return self
     */
    private function addBrandAggregation(string $type = Filter::MUST_ALL) : self
    {
        $this->aggregations['brand'] = Aggregation::create(
            'brand',
            'brand.id|brand.name',
            $type,
            false
        );

        return $this;
    }

    /**
     * Remove Brand aggregation.
     *
     * @return self
     */
    private function removeBrandAggregation() : self
    {
        unset($this->aggregations['brand']);

        return $this;
    }

    /**
     * Add categories aggregation.
     *
     * @param string $type
     *
     * @return self
     */
    private function addCategoriesAggregation(string $type = Filter::MUST_ALL) : self
    {
        $this->aggregations['categories'] = Aggregation::create(
            'categories',
            'categories.id|categories.name|categories.level',
            $type,
            true
        );

        return $this;
    }

    /**
     * Remove categories aggregation.
     *
     * @return self
     */
    private function removeCategoriesAggregation()
    {
        unset($this->aggregations['categories']);

        return $this;
    }

    /**
     * Add tags aggregation.
     *
     * @param string $groupName
     * @param array  $options
     * @param string $type
     *
     * @return self
     */
    private function addTagsAggregation(
        string $groupName,
        array $options,
        string $type = Filter::MUST_ALL
    ) : self {
        $this->aggregations[$groupName] = Aggregation::create(
            $groupName,
            'tags.name',
            $type,
            true,
            $options
        );

        return $this;
    }

    /**
     * Remove tags aggregation.
     *
     * @param string $groupName
     *
     * @return self
     */
    private function removeTagsAggregation(string $groupName)
    {
        unset($this->aggregations[$groupName]);

        return $this;
    }

    /**
     * Get aggregations.
     *
     * @return Aggregation[]
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Get aggregation.
     *
     * @param string $aggregationName
     *
     * @return Aggregation
     */
    public function getAggregation(string $aggregationName) : ? Aggregation
    {
        return $this->aggregations[$aggregationName] ?? null;
    }

    /**
     * Return Querytext.
     *
     * @return string
     */
    public function getQueryText() : string
    {
        return $this
            ->getFilter('_query')
            ->getField();
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
     * Get filter.
     *
     * @param string $filterName
     *
     * @return null|Filter
     */
    public function getFilter(string $filterName) : ? Filter
    {
        return $this->getFilters()[$filterName] ?? null;
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
