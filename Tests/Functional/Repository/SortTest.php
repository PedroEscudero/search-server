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

namespace Puntmig\Search\Server\Tests\Functional\Repository;

use Puntmig\Search\Model\Coordinate;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Query\SortBy;

/**
 * Class SortTest.
 */
trait SortTest
{
    /**
     * Test sort by price asc.
     */
    public function testSortByPriceAsc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::PRICE_ASC)),
            Product::TYPE,
            ['1', '2', '3']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByPriceRange([], ['900..1900'])->sortBy(SortBy::PRICE_ASC)),
            Product::TYPE,
            ['1', '2', '!3']
        );
    }

    /**
     * Test sort by price desc.
     */
    public function testSortByPriceDesc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::PRICE_DESC)),
            Product::TYPE,
            ['3', '2', '1']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByPriceRange([], ['900..1900'])->sortBy(SortBy::PRICE_DESC)),
            Product::TYPE,
            ['2', '1', '!3']
        );
    }

    /**
     * Test sort by discount ASC.
     */
    public function testSortByDiscountAsc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::DISCOUNT_ASC)),
            Product::TYPE,
            ['{2', '3}', '5', '1', '4']
        );
    }

    /**
     * Test sort by discount DESC.
     */
    public function testSortByDiscountDesc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::DISCOUNT_DESC)),
            Product::TYPE,
            ['4', '1', '5', '{2', '3}']
        );
    }

    /**
     * Test sort by discount percentage ASC.
     */
    public function testSortByDiscountPercentageAsc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::DISCOUNT_PERCENTAGE_ASC)),
            Product::TYPE,
            ['{2', '3}', '1', '5', '4']
        );
    }

    /**
     * Test sort by discount percentage DESC.
     */
    public function testSortByDiscountPercentageDesc()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::DISCOUNT_PERCENTAGE_DESC)),
            Product::TYPE,
            ['4', '5', '1', '{2', '3}']
        );
    }

    /**
     * Test sort by manufacturer asc.
     */
    public function testSortByManufacturerASC()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::MANUFACTURER_ASC)),
            Product::TYPE,
            ['1', '3', '4', '2', '5']
        );
    }

    /**
     * Test sort by manufacturer desc.
     */
    public function testSortByManufacturerDESC()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::MANUFACTURER_DESC)),
            Product::TYPE,
            ['5', '2', '4', '3', '1']
        );
    }

    /**
     * Test sort by brand asc.
     */
    public function testSortByBrandASC()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::BRAND_ASC)),
            Product::TYPE,
            ['1', '3', '4', '2', '5']
        );
    }

    /**
     * Test sort by brand desc.
     */
    public function testSortByBrandDESC()
    {
        $repository = static::$repository;
        $this->assertResults(
            $repository->query(Query::createMatchAll()->sortBy(SortBy::BRAND_DESC)),
            Product::TYPE,
            ['5', '2', '4', '3', '1']
        );
    }

    /**
     * Test sort by location.
     */
    public function testSortByLocationKmAsc()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createLocated(new Coordinate(45.0, 45.0), '')->sortBy(SortBy::LOCATION_KM_ASC));
        $this->assertResults(
            $result,
            Product::TYPE,
            ['3', '4', '2', '1', '5']
        );

        $products = $result->getProducts();
        $this->assertTrue($products[0]->getDistance() < 558);
        $this->assertTrue($products[0]->getDistance() > 554);
    }

    /**
     * Test sort by location.
     */
    public function testSortByLocationKmDesc()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createLocated(new Coordinate(45.0, 45.0), '')->sortBy(SortBy::LOCATION_MI_ASC));
        $this->assertResults(
            $result,
            Product::TYPE,
            ['3', '4', '2', '1', '5']
        );

        $products = $result->getProducts();
        $this->assertTrue($products[0]->getDistance() < 346);
        $this->assertTrue($products[0]->getDistance() > 344);
    }
}
