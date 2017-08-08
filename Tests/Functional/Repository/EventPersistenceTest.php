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

use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Server\Tests\Functional\PuntmigSearchServerBundleFunctionalTest;

/**
 * Class EventPersistenceTest.
 */
class EventPersistenceTest extends PuntmigSearchServerBundleFunctionalTest
{
    /**
     * Schema must be loaded in all test cases.
     *
     * @return bool
     */
    protected static function loadSchema() : bool
    {
        return true;
    }

    /**
     * Test something.
     */
    public function testEventPersistence()
    {
        $eventStore = $this->get('search_server.event_store');
        $this->assertCount(
            2,
            $eventStore->all()
        );
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertCount(
            3,
            $eventStore->all()
        );
        $this->deleteItems([new ItemUUID('2', 'product')]);
        $this->assertCount(
            4,
            $eventStore->all()
        );
        $this->query(Query::createMatchAll());
        $this->assertCount(
            5,
            $eventStore->all()
        );
    }

    /**
     * Use in memory event store.
     *
     * @return bool
     */
    protected static function useInMemoryEventStore() : bool
    {
        return false;
    }

    /**
     * get repository service name.
     *
     * @return string
     */
    protected static function getRepositoryServiceName() : string
    {
        return 'puntmig_search.repository_search';
    }
}
