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

namespace Mmoreram\SearchBundle\Tests\Functional\Repository;

use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;

/**
 * Class FiltersTest.
 */
class FiltersTest extends ElasticaSearchRepositoryTest
{
    /**
     * Test family filter.
     */
    public function testFamilyFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['product'])),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['book'])),
            Product::TYPE,
            ['?3', '!1', '!2', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['book', 'products']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['_nonexistent']))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['_nonexistent'])->filterByFamilies([])),
            Product::TYPE,
            ['?3', '?1', '?2', '?4', '?5']
        );
    }

    /**
     * Test at least one family filter.
     */
    public function testAtLeastFamilyFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['product'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['book'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?3', '!1', '!2', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['book', 'product'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?3', '?1', '?2', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['book', 'product', '_nonexistent'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?3', '?1', '?2', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByFamilies(['_nonexistent'], Filter::AT_LEAST_ONE))->getProducts()
        );
    }

    /**
     * Test type filter.
     */
    public function testTypeFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE])),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5', '!800']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE, Category::TYPE])),
            Product::TYPE,
            ['!3', '!1', '!2', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Category::TYPE])),
            Product::TYPE,
            ['!3', '!1', '!2', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Category::TYPE])),
            Category::TYPE,
            ['?1', '?2', '?3', '?50', '?66', '?777', '?778', '?800']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE]))->getCategories()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes(['_nonexistent']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByTypes([Product::TYPE]))->getProducts()
        );
    }

    /**
     * Test type filter.
     */
    public function testAtLeastOneTypeFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5', '!800']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE, Category::TYPE], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE, Category::TYPE], Filter::AT_LEAST_ONE)),
            Category::TYPE,
            ['?1', '?2', '?3', '?50', '?66', '?777', '?778', '?800']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE, Category::TYPE, Brand::TYPE], Filter::AT_LEAST_ONE)),
            Brand::TYPE,
            ['?444']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes([Product::TYPE], Filter::AT_LEAST_ONE))->getCategories()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByTypes(['_nonexistent'], Filter::AT_LEAST_ONE))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByTypes([Product::TYPE], Filter::AT_LEAST_ONE))->getProducts()
        );
    }

    /**
     * Test category filter.
     */
    public function testCategoryFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1'])),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['_4578943']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1', '_4578943']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['2', '3']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByCategories(['2', '3']))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1', '2'])),
            Product::TYPE,
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['3'])),
            Product::TYPE,
            ['?2', '!1', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1', '_4578943'])->filterByCategories([])),
            Product::TYPE,
            ['?2', '?1', '?3', '?4', '?5']
        );
    }

    /**
     * Test at least one category filter.
     */
    public function testAtLeastOneCategoryFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['_4578943'], Filter::AT_LEAST_ONE))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['1', '_4578943'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByCategories(['2', '3', '800'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '?5']
        );
    }

    /**
     * Test manufacturer filter.
     */
    public function testManufacturerFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['1'])),
            Product::TYPE,
            ['1', '!2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['1', '2']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByManufacturers(['1']))->getProducts()
        );

        $this->assertEmpty(
             $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['_4543543']))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['_4543543'])->filterByManufacturers([])),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Test at least one manufacturer filter.
     */
    public function testAtLeastOneManufacturerFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['1'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['1', '2', '3', '444'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '?3', '!4', '?5']
        );

        $this->assertEmpty(
             $repository->search(self::$key, Query::createMatchAll()->filterByManufacturers(['_4543543'], Filter::AT_LEAST_ONE))->getProducts()
        );
    }

    /**
     * Test brand filter.
     */
    public function testBrandFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['1'])),
            Product::TYPE,
            ['1', '!2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['1', '2']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByBrands(['1']))->getProducts()
        );

        $this->assertEmpty(
             $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['_4543543']))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['_4543543'])->filterByBrands([])),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Test at least one brand filter.
     */
    public function testAtLeastOneBrandFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['1'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['1', '2', '3', '10'], Filter::AT_LEAST_ONE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '!5']
        );

        $this->assertEmpty(
             $repository->search(self::$key, Query::createMatchAll()->filterByBrands(['_4543543'], Filter::AT_LEAST_ONE))->getProducts()
        );
    }

    /**
     * Test filter by price range.
     */
    public function testPriceRangeFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(1000, 2000)),
            Product::TYPE,
            ['!1', '?2', '?3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(1000, 2000)->filterByFamilies(['book'])),
            Product::TYPE,
            ['!1', '!2', '?3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(900, 1900)),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(100, 200))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::INFINITE))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::FREE))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::INFINITE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(1, PriceRange::INFINITE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::FREE)->filterByPriceRange(PriceRange::FREE, PriceRange::INFINITE)),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Test tags filter.
     */
    public function testTagFilter()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTags('_', $this->allFilters(), ['new'])),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()->filterByTags('_', $this->allFilters(), ['new', 'shirt'])),
            Product::TYPE,
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()->filterByTags('_', $this->allFilters(), ['new', 'shirt', '_nonexistent']))->getProducts()
        );

        $this->assertEmpty(
            $repository->search(self::$anotherKey, Query::createMatchAll()->filterByTags('_', $this->allFilters(), ['new']))->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()
                ->filterByTags('_', $this->allFilters(), ['new', 'shirt', '_nonexistent'])
                ->filterByTags('_', $this->allFilters(), ['new'])
            ),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->search(self::$key, Query::createMatchAll()
                ->filterByTags('_1', $this->allFilters(), ['kids'])
                ->filterByTags('_2', $this->allFilters(), ['sugar', 'last_hour'])
            )->getProducts()
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()
                ->filterByTags('_1', $this->allFilters(), ['kids'])
                ->filterByTags('_2', $this->allFilters(), ['sugar', 'last_hour'], Filter::AT_LEAST_ONE)
            ),
            Product::TYPE,
            ['!1', '!2', '?3', '!4', '?5']
        );

        $this->assertResults(
            $repository->search(self::$key, Query::createMatchAll()
                ->filterByTags('_', $this->allFilters(), ['sugar', 'kids', 'new'], Filter::AT_LEAST_ONE)
            ),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Get all filters.
     *
     * @return string[]
     */
    private function allFilters() : array
    {
        return [
            'new',
            'shirt',
            'last_hour',
            'kids',
            'sugar',
        ];
    }
}
