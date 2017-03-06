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

use Puntmig\Search\Query\Filter;
use Puntmig\Search\Query\Query;

/**
 * Class AggregationsTest.
 */
trait AggregationsTest
{
    /**
     * Test something.
     */
    public function testSomething()
    {
        $repository = static::$repository;
    }

    /**
     * Test basic aggregations.
     */
    public function testBasicAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByManufacturers(['1'], FILTER::AT_LEAST_ONE)
                ->filterByBrands([], FILTER::AT_LEAST_ONE)
        )
        ->getAggregations();
        $brandAggregation = $aggregations->getAggregation('brand');
        $manufacturerAggregation = $aggregations->getAggregation('manufacturer');

        $this->assertCount(1, $brandAggregation->getCounters());
        $this->assertCount(5, $manufacturerAggregation->getCounters());

        $this->assertEquals(
            1,
                $brandAggregation
                ->getCounter('1')
                ->getN()
        );

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByManufacturers(['1', '3'], FILTER::AT_LEAST_ONE)
                ->filterByBrands([], FILTER::AT_LEAST_ONE)
        )
        ->getAggregations();
        $brandAggregation = $aggregations->getAggregation('brand');
        $manufacturerAggregation = $aggregations->getAggregation('manufacturer');

        $this->assertCount(2, $brandAggregation->getCounters());
        $this->assertCount(5, $manufacturerAggregation->getCounters());
    }

    /**
     * Test nested aggregations.
     */
    public function testNestedAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories([])
                ->filterByCategories([])
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(2, $categoryAggregation->getCounter('1')->getN());
        $this->assertNull($categoryAggregation->getCounter('2'));
        $this->assertNull($categoryAggregation->getCounter('3'));
        $this->assertEquals(1, $categoryAggregation->getCounter('50')->getN());
        $this->assertNull($categoryAggregation->getCounter('66'));
        $this->assertEquals(1, $categoryAggregation->getCounter('777')->getN());
        $this->assertNull($categoryAggregation->getCounter('778'));
        $this->assertEquals(1, $categoryAggregation->getCounter('800')->getN());

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['1'])
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertNull($categoryAggregation->getCounter('1'));
        $this->assertEquals(1, $categoryAggregation->getCounter('2')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('3')->getN());
        $this->assertNull($categoryAggregation->getCounter('50'));
        $this->assertNull($categoryAggregation->getCounter('66'));
        $this->assertNull($categoryAggregation->getCounter('777'));
        $this->assertNull($categoryAggregation->getCounter('778'));
        $this->assertNull($categoryAggregation->getCounter('800'));

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['1'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(2, $categoryAggregation->getCounter('1')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('2')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('3')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('50')->getN());

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories([], Filter::MUST_ALL_WITH_LEVELS)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(2, $categoryAggregation->getCounter('1')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('50')->getN());
        $this->assertCount(2, $categoryAggregation->getCounters());

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['1'], Filter::MUST_ALL_WITH_LEVELS)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(1, $categoryAggregation->getCounter('2')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('3')->getN());
        $this->assertCount(2, $categoryAggregation->getCounters());

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['50'], Filter::MUST_ALL_WITH_LEVELS)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(1, $categoryAggregation->getCounter('66')->getN());
        $this->assertCount(1, $categoryAggregation->getCounters());
    }

    /**
     * Test Tag filter aggregations.
     */
    public function testTagsFilterAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByTags('specials', ['new', 'last_hour'], [], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $this->assertEquals(
            2,
            $aggregations->getAggregation('specials')->getCounter('new')->getN()
        );
        $this->assertEquals(
            1,
            $aggregations->getAggregation('specials')->getCounter('last_hour')->getN()
        );

        $this->assertCount(
            3,
            $repository->query(
                Query::createMatchAll()
                    ->filterByTags('specials', ['new', 'last_hour'], ['new', 'last_hour'], Filter::AT_LEAST_ONE)
            )->getProducts()
        );

        $this->assertCount(
            1,
            $repository->query(
                Query::createMatchAll()
                    ->filterByTags('specials', ['new', 'last_hour'], ['last_hour'], Filter::AT_LEAST_ONE)
            )->getProducts()
        );

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByTags('specials', ['new', 'shirt'], [], Filter::MUST_ALL)
        )
        ->getAggregations();
        $this->assertEquals(
            2,
            $aggregations->getAggregation('specials')->getCounter('new')->getN()
        );
        $this->assertEquals(
            1,
            $aggregations->getAggregation('specials')->getCounter('shirt')->getN()
        );

        $this->assertCount(
            1,
            $repository->query(
                Query::createMatchAll()
                    ->filterByTags('specials', ['new', 'shirt'], ['new', 'shirt'], Filter::MUST_ALL)
            )->getProducts()
        );
    }

    /**
     * Test leveled filter and aggregations.
     */
    public function testLeveledFilterAndAggregation()
    {
        $repository = static::$repository;
        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['1', '2'])
        )
        ->getAggregations();

        $usedCategoryElements = $aggregations->getAggregation('categories')->getActiveElements();
        $this->assertCount(
            1,
            $usedCategoryElements
        );
        $firstUsedCategoryElements = reset($usedCategoryElements);
        $this->assertEquals('2', $firstUsedCategoryElements->getId());
        $this->assertTrue($firstUsedCategoryElements->isUsed());

        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterByCategories(['1'])
        )
        ->getAggregations();

        $usedCategoryElements = $aggregations->getAggregation('categories')->getActiveElements();
        $this->assertCount(
            1,
            $usedCategoryElements
        );
        $firstUsedCategoryElements = reset($usedCategoryElements);
        $this->assertEquals('1', $firstUsedCategoryElements->getId());
        $this->assertTrue($firstUsedCategoryElements->isUsed());
    }
}
