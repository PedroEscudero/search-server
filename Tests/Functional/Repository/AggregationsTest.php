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

use Mmoreram\SearchBundle\Query\Filter;
use Mmoreram\SearchBundle\Query\Query;

/**
 * Class AggregationsTest.
 */
class AggregationsTest extends ElasticaSearchRepositoryTest
{
    /**
     * Test basic aggregations.
     */
    public function testBasicAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByManufacturers(['1'])
                ->filterByBrands([])
        )
        ->getAggregations();

        $this->assertEquals(
            1,
            $aggregations
                ->getAggregation('brand')
                ->getCounter('1')
                ->getN()
        );
    }

    /**
     * Test nested aggregations.
     */
    public function testNestedAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByCategories([])
                ->removeCategoriesFilter()
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

        $aggregations = $repository->search(
            '000',
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

        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByCategories(['1'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(2, $categoryAggregation->getCounter('1')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('2')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('3')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('50')->getN());

        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByCategories([], Filter::MUST_ALL)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(2, $categoryAggregation->getCounter('1')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('50')->getN());
        $this->assertCount(2, $categoryAggregation->getCounters());

        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByCategories(['1'], Filter::MUST_ALL)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(1, $categoryAggregation->getCounter('2')->getN());
        $this->assertEquals(1, $categoryAggregation->getCounter('3')->getN());
        $this->assertCount(2, $categoryAggregation->getCounters());

        $aggregations = $repository->search(
            '000',
            Query::createMatchAll()
                ->filterByCategories(['50'], Filter::MUST_ALL)
                ->filterByBrands(['1', '2', '3'], Filter::AT_LEAST_ONE)
        )
        ->getAggregations();

        $categoryAggregation = $aggregations->getAggregation('categories');
        $this->assertEquals(1, $categoryAggregation->getCounter('66')->getN());
        $this->assertCount(1, $categoryAggregation->getCounters());
    }
}
