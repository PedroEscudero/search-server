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

namespace Apisearch\Server\Domain\Event;

/**
 * Class CollectInMemoryDomainEventSubscriber.
 */
class CollectInMemoryDomainEventSubscriber implements EventSubscriber
{
    /**
     * @var DomainEvent[]
     *
     * Events
     */
    private $events = [];

    /**
     * Subscriber should handle event.
     *
     * @param DomainEvent $event
     *
     * @return bool
     */
    public function shouldHandleEvent(DomainEvent $event): bool
    {
        return true;
    }

    /**
     * Handle event.
     *
     * @param DomainEvent $event
     */
    public function handle(DomainEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * Get Events.
     *
     * @return DomainEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
