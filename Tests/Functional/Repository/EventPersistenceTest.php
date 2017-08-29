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
    protected static function loadSchema(): bool
    {
        return true;
    }

    /**
     * Test something.
     */
    public function testEventPersistence()
    {
        $eventRepository = $this->get('puntmig_search.event_repository_search');
        $this->assertCount(
            2,
            $eventRepository->all()
        );
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertCount(
            3,
            $eventRepository->all()
        );
        $this->deleteItems([new ItemUUID('2', 'product')]);
        $this->assertCount(
            4,
            $eventRepository->all()
        );
        $this->query(Query::createMatchAll());
        $this->assertCount(
            5,
            $eventRepository->all()
        );
    }

    /**
     * get repository service name.
     *
     * @return string
     */
    protected static function getRepositoryServiceName(): string
    {
        return 'puntmig_search.repository_search';
    }
}
