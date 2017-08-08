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

namespace Puntmig\Search\Server\Domain\Event;

/**
 * Class InMemoryEventStore.
 */
class InMemoryEventStore implements EventStore
{
    /**
     * Events.
     */
    private $events = [];

    /**
     * Append event.
     *
     * @param DomainEvent      $event
     * @param null|DomainEvent $previousEvent
     */
    public function append(
        DomainEvent $event,
        ? DomainEvent $previousEvent = null
    ) {
        $this->events[] = $event;
    }

    /**
     * Get all events.
     *
     * @param int $length
     * @param int $offset
     *
     * @return DomainEvent[]
     */
    public function all(
        int $length = 10,
        int $offset = 0
    ) : array {
        return array_slice(
            $this->events,
            $offset,
            $length
        );
    }

    /**
     * Empty events.
     */
    public function reset()
    {
        $this->events = [];
    }
}
