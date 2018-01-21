<?php

/*
 * This file is part of the Apisearch Server
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

namespace Apisearch\Server\Tests\Functional\Repository;

use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query;
use Apisearch\Server\Tests\Functional\ApisearchServerBundleFunctionalTest;

/**
 * Class EventPersistenceTest.
 */
class EventPersistenceTest extends ApisearchServerBundleFunctionalTest
{
    /**
     * Get domain events middleware service.
     *
     * @return string
     */
    protected static function getDomainEventsMiddlewareService(): string
    {
        return 'apisearch_server.middleware.inline_events';
    }

    /**
     * Test something.
     */
    public function testEventPersistence()
    {
        $eventRepository = self::get('apisearch_server.events_repository');
        $this->assertCount(1, $eventRepository->query(Query::createMatchAll())->getEvents());

        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertCount(2, $eventRepository->query(Query::createMatchAll())->getEvents());
        $this->deleteItems([new ItemUUID('2', 'product')]);
        $this->assertCount(3, $eventRepository->query(Query::createMatchAll())->getEvents());
        $this->query(Query::createMatchAll());
        $this->assertCount(4, $eventRepository->query(Query::createMatchAll())->getEvents());
        $this->resetIndex();
        $this->assertCount(5, $eventRepository->query(Query::createMatchAll())->getEvents());
        $this->resetIndex();
        $this->assertCount(6, $eventRepository->query(Query::createMatchAll())->getEvents());
    }
}
