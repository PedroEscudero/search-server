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

use Puntmig\Search\Geo\CoordinateAndDistance;
use Puntmig\Search\Geo\Polygon;
use Puntmig\Search\Geo\Square;
use Puntmig\Search\Model\Coordinate;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Query\Query;

/**
 * Class LocationFiltersTest.
 */
trait LocationFiltersTest
{
    /**
     * Test location filter with a simple coordinate and a distance.
     */
    public function testLocationFilterCoordinateAndDistance()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '100km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '557km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '!2', '?3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '1180km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '!2', '?3', '?4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '1320km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '?2', '?3', '?4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '2123km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new CoordinateAndDistance(
                    new Coordinate(45.0, 45.0),
                    '2350km'
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Test location filter with a Square filter.
     */
    public function testLocationFilterSquare()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Square(
                    new Coordinate(46.0, 44.0),
                    new Coordinate(44.0, 46.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Square(
                    new Coordinate(61.0, 29.0),
                    new Coordinate(29.0, 61.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Square(
                    new Coordinate(61.0, 29.0),
                    new Coordinate(29.0, 71.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '?5']
        );
    }

    /**
     * Test location filter with a polygon filter.
     */
    public function testLocationFilterPolygon()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Polygon(
                    new Coordinate(46.0, 44.0),
                    new Coordinate(44.0, 44.0),
                    new Coordinate(44.0, 46.0),
                    new Coordinate(46.0, 46.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['!1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Polygon(
                    new Coordinate(61.0, 29.0),
                    new Coordinate(29.0, 29.0),
                    new Coordinate(29.0, 61.0),
                    new Coordinate(61.0, 61.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '?3', '?4', '!5']
        );

        $this->assertResults(
            $repository->query($this->createLocatedQuery()->filterByLocation(
                new Polygon(
                    new Coordinate(61.0, 29.0),
                    new Coordinate(29.0, 29.0),
                    new Coordinate(60.5, 72.0),
                    new Coordinate(70.0, 45.0)
                ),
                [],
                []
            )),
            Product::TYPE,
            ['?1', '?2', '!3', '!4', '?5']
        );
    }

    /**
     * Create located query with [45,45].
     *
     * @return Query
     */
    private function createLocatedQuery() : Query
    {
        return Query::createLocated(new Coordinate(45.0, 45.0), '');
    }
}
