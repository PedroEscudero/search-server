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

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Model\Metadata;
use Puntmig\Search\Query\Aggregation;
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
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->filterBy('color', 'color', ['pink'], FILTER::AT_LEAST_ONE)
            )
            ->getAggregations();

        $aggregation = $aggregations->getAggregation('color');
        $this->assertCount(4, $aggregation->getCounters());
        $this->assertSame(
            1,
                $aggregation
                ->getCounter('pink')
                ->getN()
        );

        $this->assertSame(
            2,
                $aggregation
                ->getCounter('yellow')
                ->getN()
        );
    }

    /**
     * Test aggregations with null value.
     */
    public function testNullAggregation()
    {
        $repository = static::$repository;
        $aggregations = $repository->query(
            Query::createMatchAll()
                ->filterBy('nonexistent', 'nonexistent', [])
        )
        ->getAggregations();

        $this->assertEmpty($aggregations
            ->getAggregation('nonexistent')
            ->getCounters()
        );
    }

    /**
     * Test disable aggregations.
     */
    public function testDisableAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->filterBy('color', 'color', ['1'], FILTER::AT_LEAST_ONE)
                    ->disableAggregations()
            )
            ->getAggregations();

        $this->assertNull($aggregations);
    }

    /**
     * Test editorial.
     */
    public function testEditorialAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->aggregateBy(
                        'editorial',
                        'editorial_data',
                        FILTER::AT_LEAST_ONE
                    )
            )
            ->getAggregations();

        $this->assertCount(3, $aggregations->getAggregation('editorial')->getCounters());
    }

    /**
     * Test aggregation with one to many field.
     */
    public function testSimpleOneToManyAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->aggregateBy(
                        'stores',
                        'stores',
                        FILTER::AT_LEAST_ONE
                    )
            )
            ->getAggregations();

        $this->assertCount(4, $aggregations->getAggregation('stores')->getCounters());
    }

    /**
     * Test aggregation with several fields.
     */
    public function testAuthorAggregations()
    {
        $repository = static::$repository;
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->aggregateBy(
                        'author',
                        'author_data',
                        FILTER::AT_LEAST_ONE
                    )
            )
            ->getAggregations();

        $this->assertCount(3, $aggregations->getAggregation('author')->getCounters());
    }

    /**
     * Test aggregation with metadata format conversion.
     */
    public function testAggregationWithMetadataFormatConversion()
    {
        $repository = static::$repository;
        $repository->addItem(Item::create(
            new ItemUUID('1', 'testing'),
            [],
            [
                'author_data' => [
                    0 => Metadata::toMetadata([
                        'id' => 777,
                        'name' => 'Engonga',
                        'last_name' => 'Efervescencio',
                    ]),
                ],
            ]
        ));

        $repository->flush();
        $aggregations = $repository
            ->query(
                Query::createMatchAll()
                    ->aggregateBy(
                        'author',
                        'author_data',
                        FILTER::AT_LEAST_ONE
                    )
            )
            ->getAggregations();

        $this->assertCount(4, $aggregations->getAggregation('author')->getCounters());

        /**
         * Reseting scenario for next calls.
         */
        self::resetScenario();
    }

    /**
     * Test leveled aggregations.
     */
    public function testLeveledAggregations()
    {
        $repository = static::$repository;
        $aggregation = $repository
            ->query(
                Query::createMatchAll()
                    ->aggregateBy('category', 'category_data', FILTER::MUST_ALL_WITH_LEVELS)
            )
            ->getAggregation('category');
        $this->assertCount(2, $aggregation->getCounters());
        $this->assertTrue(array_key_exists('1', $aggregation->getCounters()));
        $this->assertTrue(array_key_exists('7', $aggregation->getCounters()));

        $aggregation = $repository
            ->query(
                Query::createMatchAll()
                    ->filterBy('category', 'category', ['1'], FILTER::MUST_ALL_WITH_LEVELS)
                    ->aggregateBy('category', 'category_data', FILTER::MUST_ALL_WITH_LEVELS)
            )
            ->getAggregation('category');
        $this->assertCount(2, $aggregation->getCounters());
        $this->assertTrue(array_key_exists('2', $aggregation->getCounters()));
        $this->assertTrue(array_key_exists('5', $aggregation->getCounters()));

        $aggregation = $repository
            ->query(
                Query::createMatchAll()
                    ->filterBy('category', 'category', ['2'], FILTER::MUST_ALL_WITH_LEVELS)
                    ->aggregateBy('category', 'category_data', FILTER::MUST_ALL_WITH_LEVELS)
            )
            ->getAggregation('category');
        $this->assertCount(2, $aggregation->getCounters());
        $this->assertTrue(array_key_exists('3', $aggregation->getCounters()));
        $this->assertTrue(array_key_exists('4', $aggregation->getCounters()));
    }

    /**
     * Aggregate by date.
     */
    public function testDateRangeAggregations()
    {
        $this->assertCount(
            1,
            static::$repository->query(Query::createMatchAll()
                ->filterUniverseByDateRange('created_at', ['2020-02-02..2020-04-04'], Filter::AT_LEAST_ONE)
                ->aggregateByDateRange('created_at', 'created_at', ['2020-03-03..2020-04-04'], Filter::AT_LEAST_ONE)
            )->getAggregation('created_at')
        );

        $this->assertCount(
            2,
            static::$repository->query(Query::createMatchAll()
                ->filterUniverseByDateRange('created_at', ['2020-02-02..2020-04-04'], Filter::AT_LEAST_ONE)
                ->aggregateByDateRange('created_at', 'created_at', ['2020-02-02..2020-03-03', '2020-03-03..2020-04-04'], Filter::AT_LEAST_ONE)
            )->getAggregation('created_at')
        );
    }

    /**
     * Test aggregation sort.
     *
     * @param int   $firstId
     * @param array $order
     *
     * @dataProvider dataSort
     */
    public function testSort(
        int $firstId,
        ? array $order
    ) {
        $query = Query::createMatchAll();
        is_null($order)
            ? $query->aggregateBy('sortable', 'sortable_data', FILTER::AT_LEAST_ONE)
            : $query->aggregateBy('sortable', 'sortable_data', FILTER::AT_LEAST_ONE, $order);

        $counters = static::$repository
            ->query($query)
            ->getAggregation('sortable')
            ->getCounters();

        $firstCounter = reset($counters);
        $this->assertEquals($firstId, $firstCounter->getId());
    }

    /**
     * data for testSort.
     */
    public function dataSort()
    {
        return [
            ['3', null],
            ['3', Aggregation::SORT_BY_COUNT_DESC],
            ['1', Aggregation::SORT_BY_COUNT_ASC],
            ['9', Aggregation::SORT_BY_NAME_DESC],
            ['1', Aggregation::SORT_BY_NAME_ASC],
        ];
    }
}
