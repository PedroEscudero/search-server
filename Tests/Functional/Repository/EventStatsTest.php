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
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Tests\Functional\ApisearchServerBundleFunctionalTest;

/**
 * Class EventStatsTest.
 */
class EventStatsTest extends ApisearchServerBundleFunctionalTest
{
    /**
     * Test something.
     */
    public function testEventStats()
    {
        $eventRepository = self::get('apisearch.server.event_repository');
        $eventRepository->setRepositoryReference(RepositoryReference::create(
            self::$appId,
            self::$index
        ));
        $eventRepository->createRepository(true);

        $this->reset();
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->query(Query::createMatchAll());
        $this->deleteItems([new ItemUUID('2', 'product')]);
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->query(Query::createMatchAll());
        $this->reset();
        $this->reset();

        $stats = $eventRepository->stats();
        $this->assertEquals(3, $stats->getEventCounter()['IndexWasReset']);
        $this->assertEquals(4, $stats->getEventCounter()['ItemsWereDeleted']);
        $this->assertFalse(array_key_exists('ItemsWereIndexed', $stats->getEventCounter()));
        $this->assertEquals(2, $stats->getEventCounter()['QueryWasMade']);
    }
}
