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

use Apisearch\Event\EventRepository;
use Apisearch\Event\Stats;
use Apisearch\Server\Domain\Query\StatsEvents;

/**
 * Class StatsEventsHandler.
 */
class StatsEventsHandler
{
    /**
     * @var EventRepository
     *
     * Event repository
     */
    private $eventRepository;

    /**
     * ListEventsHandler constructor.
     *
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Reset the query.
     *
     * @param StatsEvents $statsEvents
     *
     * @return Stats
     */
    public function handle(StatsEvents $statsEvents)
    {
        $this
            ->eventRepository
            ->setRepositoryReference($statsEvents->getRepositoryReference());

        return $this
            ->eventRepository
            ->stats(
                $statsEvents->getFrom(),
                $statsEvents->getTo()
            );
    }
}
