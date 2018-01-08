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

namespace Apisearch\Server\Elastica\EventRepository;

use Apisearch\Event\Event;
use Apisearch\Event\EventRepository as BaseEventRepository;
use Apisearch\Event\SortBy;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryWithCredentials;
use Apisearch\Result\Events;
use Apisearch\Server\Elastica\WithRepositories;
use Elastica\Query as ElasticaQuery;

/**
 * Class Repository.
 */
class Repository extends RepositoryWithCredentials implements BaseEventRepository
{
    use WithRepositories;

    /**
     * Create an index.
     *
     * @throws ResourceExistsException
     */
    public function createIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->createIndex();
    }

    /**
     * Delete an index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->deleteIndex();
    }

    /**
     * Save event.
     *
     * @param Event $event
     *
     * @throws ResourceNotAvailableException
     */
    public function save(Event $event)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->addEvent($event);
    }

    /**
     * Query over events
     *
     * @param Query    $query
     * @param int|null $from
     * @param int|null $to
     *
     * @return Events
     *
     * @throws ResourceNotAvailableException
     */
    public function query(
        Query $query,
        ? int $from = null,
        ? int $to = null
    ): Events {
        return $this
            ->getRepository(QueryRepository::class)
            ->query(
                $query,
                $from,
                $to
            );
    }

    /**
     * Get last event.
     *
     * @return Event|null
     *
     * @throws ResourceNotAvailableException
     */
    public function last(): ? Event
    {
        $query = Query::create('', 0, 1);
        $query->sortBy(SortBy::OCCURRED_ON_DESC);

        return $this
            ->getRepository(QueryRepository::class)
            ->query($query, null, null)
            ->getFirstEvent();
    }
}
