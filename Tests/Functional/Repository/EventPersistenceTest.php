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
 * Class EventPersistenceTest.
 */
class EventPersistenceTest extends ApisearchServerBundleFunctionalTest
{
    /**
     * Test something.
     */
    public function testEventPersistence()
    {
        $eventRepository = self::get('apisearch.server.event_repository');
        $eventRepository->setRepositoryReference(RepositoryReference::create(
            self::$appId,
            self::$index
        ));
        $eventRepository->createRepository(true);

        $this->reset();
        $this->assertCount(
            1,
            $eventRepository->all()
        );
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertCount(
            2,
            $eventRepository->all()
        );
        $this->deleteItems([new ItemUUID('2', 'product')]);
        $this->assertCount(
            3,
            $eventRepository->all()
        );
        $this->query(Query::createMatchAll());
        $this->assertCount(
            4,
            $eventRepository->all()
        );
        $this->reset();
        $this->assertCount(
            5,
            $eventRepository->all()
        );
        $this->reset();
        $this->assertCount(
            6,
            $eventRepository->all()
        );

        $eventRepository->setRepositoryReference(RepositoryReference::create(
            self::$anotherAppId,
            self::$index
        ));
        $eventRepository->createRepository(true);
        $this->assertCount(
            0,
            $eventRepository->all()
        );

        $eventRepository->setRepositoryReference(RepositoryReference::create(
            self::$anotherAppId,
            self::$anotherIndex
        ));
        $eventRepository->createRepository(true);
        $this->assertCount(
            0,
            $eventRepository->all()
        );

        $eventRepository->setRepositoryReference(RepositoryReference::create(
            self::$appId,
            self::$index
        ));
        $this->assertCount(
            6,
            $eventRepository->all()
        );
    }
}
