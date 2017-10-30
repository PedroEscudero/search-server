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

namespace Puntmig\Search\Server\Domain\QueryHandler;

use Puntmig\Search\Event\Event;
use Puntmig\Search\Event\EventRepository;
use Puntmig\Search\Server\Domain\Query\ListEvents;

/**
 * Class ListEventsHandler.
 */
class ListEventsHandler
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
     * @param ListEvents $listEvents
     *
     * @return Event[]
     */
    public function handle(ListEvents $listEvents)
    {
        return $this
            ->eventRepository
            ->all(
                $listEvents->getAppId(),
                $listEvents->getKey(),
                $listEvents->getName(),
                $listEvents->getFrom(),
                $listEvents->getTo(),
                $listEvents->getLength(),
                $listEvents->getOffset()
            );
    }
}
