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
 * Class EventStatsTest.
 */
class EventStatsTest extends PuntmigSearchServerBundleFunctionalTest
{
    /**
     * Test something.
     */
    public function testEventStats()
    {
        $eventRepository = self::get('search_server.event_repository');
        $eventRepository->setAppId(self::$appId);
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
        $this->assertEquals(0, $stats->getEventCounter()['ItemsWereIndexed']);
        $this->assertEquals(2, $stats->getEventCounter()['QueryWasMade']);
    }
}
