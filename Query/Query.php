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
     * @var Filter[]
     *
     * Filters
     */
    private $filters = [];

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
     * @param $queryText
     */
    private function __construct($queryText)
    {
        $this->filters['_query'] = Filter::create(
            '',
            [$queryText],
            0,
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
        $query = new self('');
        $query->from = 0;
        $query->size = 1000;

        return $query;
    }

    /**
     * Filter by families.
     *
     * @param array $families
     * @param int   $filterType
     *
     * @return self
     */
    public function filterByFamilies(
        array $families,
        int $filterType = Filter::MUST_ALL
    ) : self {
        if (!empty($families)) {
            $this->filters['family'] = Filter::create(
                'family',
                $families,
                $filterType,
                Filter::TYPE_FIELD
            );
        } else {
            unset($this->filters['family']);
        }

        return $this;
    }

    /**
     * Filter by types.
     *
     * @param array $types
     * @param int   $filterType
     *
     * @return self
     */
    public function filterByTypes(
        array $types,
        int $filterType = Filter::MUST_ALL
    ) : self {
        if (!empty($types)) {
            $this->filters['type'] = Filter::create(
                '_type',
                $types,
                $filterType,
                Filter::TYPE_FIELD
            );
        } else {
            unset($this->filters['type']);
        }

        return $this;
    }

    /**
     * Filter by categories.
     *
     * @param array $categories
     * @param int   $filterType
     *
     * @return self
     */
    public function filterByCategories(
        array $categories,
        int $filterType = Filter::MUST_ALL_WITH_LEVELS
    ) : self {
        if (!empty($categories)) {
            $this->filters['categories'] = Filter::create(
                'categories.id',
                $categories,
                $filterType,
                Filter::TYPE_NESTED
            );
        } else {
            unset($this->filters['categories']);
        }

        $this->addCategoriesAggregation($filterType);

        return $this;
    }

    /**
     * Filter by manufacturer.
     *
     * @param array $manufacturers
     * @param int   $filterType
     *
     * @return self
     */
    public function filterByManufacturers(
        array $manufacturers,
        int $filterType = Filter::MUST_ALL
    ) : self {
        if (!empty($manufacturers)) {
            $this->filters['manufacturer'] = Filter::create(
                'manufacturer.id',
                $manufacturers,
                $filterType,
                Filter::TYPE_FIELD
            );
        } else {
            unset($this->filters['manufacturer']);
        }

        $this->addManufacturerAggregation($filterType);

        return $this;
    }

    /**
     * Filter by brand.
     *
     * @param array $brands
     * @param int   $filterType
     *
     * @return self
     */
    public function filterByBrands(
        array $brands,
        int $filterType = Filter::MUST_ALL
    ) : self {
        if (!empty($brands)) {
            $this->filters['brand'] = Filter::create(
                'brand.id',
                $brands,
                $filterType,
                Filter::TYPE_FIELD
            );
        } else {
            unset($this->filters['brand']);
        }

        $this->addBrandAggregation($filterType);

        return $this;
    }

    /**
     * Filter by tags.
     *
     * @param string $groupName
     * @param array  $options
     * @param array  $tags
     * @param int    $filterType
     *
     * @return self
     */
    public function filterByTags(
        string $groupName,
        array $options,
        array $tags,
        int $filterType = Filter::MUST_ALL
    ) : self {
        if (!empty($tags)) {
            $this->filters[$groupName] = Filter::create(
                'tags.name',
                $tags,
                $filterType,
                Filter::TYPE_NESTED,
                [
                    'tags.name',
                    $options,
                ]
            );
        } else {
            unset($this->filters[$groupName]);
        }

        $this->addTagsAggregation($groupName, $options, $filterType);

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
        if (
            $from !== PriceRange::FREE ||
            $to !== PriceRange::INFINITE
        ) {
            $this->filters['price_range'] = Filter::create(
                'real_price',
                [
                    'from' => $from,
                    'to' => $to,
                ],
                0,
                Filter::TYPE_RANGE
            );
        } else {
            unset($this->filters['price_range']);
        }

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
     * @param int $type
     *
     * @return self
     */
    private function addManufacturerAggregation(int $type = Filter::MUST_ALL) : self
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
     * Add Brand aggregation.
     *
     * @param int $type
     *
     * @return self
     */
    private function addBrandAggregation(int $type = Filter::MUST_ALL) : self
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
     * Add categories aggregation.
     *
     * @param int $type
     *
     * @return self
     */
    private function addCategoriesAggregation(int $type = Filter::MUST_ALL) : self
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
     * Add tags aggregation.
     *
     * @param string $groupName
     * @param array  $options
     * @param int    $type
     *
     * @return self
     */
    private function addTagsAggregation(
        string $groupName,
        array $options,
        int $type = Filter::MUST_ALL
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
        return $this->filters;
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
