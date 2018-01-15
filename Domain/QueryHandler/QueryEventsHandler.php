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

namespace Apisearch\Server\Domain\QueryHandler;

use Apisearch\Result\Events;
use Apisearch\Server\Domain\Query\QueryEvents;
use Apisearch\Server\Domain\WithEventRepository;

/**
 * Class QueryEventsHandler.
 */
class QueryEventsHandler extends WithEventRepository
{
    /**
     * Query events.
     *
     * @param QueryEvents $queryEvents
     *
     * @return Events
     */
    public function handle(QueryEvents $queryEvents): Events
    {
        $this
            ->eventRepository
            ->setRepositoryReference($queryEvents->getRepositoryReference());

        return $this
            ->eventRepository
            ->query(
                $queryEvents->getQuery(),
                $queryEvents->getFrom(),
                $queryEvents->getTo()
            );
    }
}
