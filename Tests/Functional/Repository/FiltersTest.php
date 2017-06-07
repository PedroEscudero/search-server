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
 * Class FiltersTest.
 */
trait FiltersTest
{
    /**
     * Filter by simple fields.
     */
    public function testFilterBySimpleFields()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByIds(['1'])),
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByIds(['1', '2'])),
            ['?1', '?2', '!3', '!4', '!5']
        );
    }

    /**
     * Filter by metadata fields.
     */
    public function testFilterBydataFields()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('i', 'field_integer', ['10'], Filter::MUST_ALL)),
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('b', 'field_boolean', ['true'], Filter::MUST_ALL)),
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('k', 'field_keyword', ['my_keyword'], Filter::MUST_ALL)),
            ['?1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('color', 'color', ['yellow'], Filter::AT_LEAST_ONE)),
            ['!1', '!2', '?3', '!4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('color', 'color', ['yellow', 'red'], Filter::MUST_ALL)),
            ['!1', '!2', '!3', '!4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('color', 'color', ['yellow', 'nonexistent'], Filter::MUST_ALL)),
            ['!1', '!2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterBy('color', 'color', ['nonexistent'], Filter::AT_LEAST_ONE)),
            ['!1', '!2', '!3', '!4', '!5']
        );
    }

    /**
     * Test type filter.
     */
    public function testTypeFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByTypes(['product'])),
            ['?1', '?2', '!3', '!4', '!5', '!800']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByTypes(['product', 'book'])),
            ['?1', '?2', '?3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByTypes(['book'])),
            ['!1', '!2', '?3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->query(Query::createMatchAll()->filterByTypes(['_nonexistent']))->getItems()
        );

        $repository->setKey(self::$anotherKey);
        $this->assertEmpty(
            $repository->query(Query::createMatchAll()->filterByTypes(['product']))->getItems()
        );
    }

    /**
     * Test filter by price range.
     */
    public function testPriceRangeFilter()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['1000..2000'])),
            ['!1', '?2', '!3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['1000..2001'])->filterByTypes(['book'])),
            ['!1', '!2', '?3', '!4', '!5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['900..1900'])),
            ['?1', '?2', '!3', '!4', '!5']
        );

        $this->assertEmpty(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['100..200']))->getItems()
        );

        $this->assertEmpty(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['0..1']))->getItems()
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['0..-1'])),
            ['?1', '?2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['1..-1'])),
            ['?1', '?2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['0..0'])->filterByRange('price', 'price', [], ['0..-1'])),
            ['?1', '?2', '?3', '?4', '?5']
        );

        $repository->setKey(self::$anotherKey);
        $this->assertEmpty(
            $repository->query(Query::createMatchAll()->filterByRange('price', 'price', [], ['0..-1']))->getItems()
        );
    }
}
