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

use Apisearch\Event\Event;
use Apisearch\Server\Domain\Query\ListEvents;
use Apisearch\Server\Domain\WithEventRepository;

/**
 * Class ListEventsHandler.
 */
class ListEventsHandler extends WithEventRepository
{
    /**
     * Reset the query.
     *
     * @param ListEvents $listEvents
     *
     * @return Event[]
     */
    public function handle(ListEvents $listEvents)
    {
        $this
            ->eventRepository
            ->setRepositoryReference($listEvents->getRepositoryReference());

        return $this
            ->eventRepository
            ->all(
                $listEvents->getName(),
                $listEvents->getFrom(),
                $listEvents->getTo(),
                $listEvents->getLength(),
                $listEvents->getOffset()
            );
    }
}
