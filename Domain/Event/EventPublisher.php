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
 * Class EventPublisher.
 */
class EventPublisher
{
    /**
     * @var EventSubscriber[]
     *
     * Subscribers
     */
    private $subscribers = [];

    /**
     * Add subscriber.
     *
     * @param EventSubscriber $subscriber
     */
    public function subscribe(EventSubscriber $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    /**
     * Publish event.
     *
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event)
    {
        foreach ($this->subscribers as $subscriber) {
            if ($subscriber->shouldHandleEvent($event)) {
                $subscriber->handle($event);
            }
        }
    }
}
