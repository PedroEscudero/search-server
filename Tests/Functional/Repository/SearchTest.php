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
use Puntmig\Search\Query\Query;
use Puntmig\Search\Query\User;

/**
 * Class SearchTest.
 */
trait SearchTest
{
    /**
     * Test get match all.
     */
    public function testMatchAll()
    {
        $result = $this->query(Query::createMatchAll());
        $this->assertSame(
            count($result->getItems()),
            $this->get('search_server.elastica_wrapper')->getType(self::$appId, 'item')->count()
        );
    }

    /**
     * Test basic search.
     */
    public function testBasicSearch()
    {
        $result = $this->query(Query::create('badal'));
        $this->assertNTypeElementId($result, 0, '5');
    }

    /**
     * Test basic search with all results method call.
     */
    public function testAllResults()
    {
        $results = $this
            ->query(Query::create('barcelona'))
            ->getItems();

        $this->assertCount(1, $results);
        $this->assertInstanceof(Item::class, $results[0]);
    }

    /**
     * Test search by reference.
     */
    public function testSearchByReference()
    {
        $result = $this->query(Query::createByUUID(new ItemUUID('4', 'bike')));
        $this->assertCount(1, $result->getItems());
        $this->assertSame('4', $result->getItems()[0]->getUUID()->getId());
        $this->assertSame('bike', $result->getItems()[0]->getUUID()->getType());
    }

    /**
     * Test search by references.
     */
    public function testSearchByReferences()
    {
        $result = $this->query(Query::createByUUIDs([
            new ItemUUID('5', 'gum'),
            new ItemUUID('3', 'book'),
        ]));
        $this->assertCount(2, $result->getItems());
        $this->assertSame('5', $result->getItems()[0]->getUUID()->getId());
        $this->assertSame('3', $result->getItems()[1]->getUUID()->getId());

        $result = $this->query(Query::createByUUIDs([
            new ItemUUID('5', 'gum'),
            new ItemUUID('5', 'gum'),
        ]));
        $this->assertCount(1, $result->getItems());
        $this->assertSame('5', $result->getItems()[0]->getUUID()->getId());
    }

    /**
     * Test query user.
     */
    public function testQueryUser()
    {
        $result = $this->query(Query::createByUUIDs([
            new ItemUUID('5', 'gum'),
            new ItemUUID('3', 'book'),
        ])->byUser(new User('123')));
        $this->assertCount(2, $result->getItems());
        $this->assertEquals(
            '123',
            $result->getQuery()->getUser()->getId()
        );

        $result = $this->query(Query::createByUUIDs([
            new ItemUUID('5', 'gum'),
            new ItemUUID('3', 'book'),
        ])->byUser(new User('123'))->anonymously());
        $this->assertCount(2, $result->getItems());
        $this->assertNull(
            $result->getQuery()->getUser()
        );
    }

    /**
     * Test accents.
     */
    public function testAccents()
    {
        $this->assertEquals(
            3,
            $this
                ->query(Query::create('codigo'))
                ->getFirstItem()
                ->getId()
        );

        $this->assertEquals(
            3,
            $this
                ->query(Query::create('código'))
                ->getFirstItem()
                ->getId()
        );
    }
}
