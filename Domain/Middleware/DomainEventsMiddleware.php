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

namespace Puntmig\Search\Server\Domain\Middleware;

use League\Tactician\Middleware;

use Puntmig\Search\Server\Domain\Event\CollectInMemoryDomainEventSubscriber;
use Puntmig\Search\Server\Domain\Event\DomainEvent;
use Puntmig\Search\Server\Domain\Event\EventPublisher;
use Puntmig\Search\Server\Domain\Event\EventStore;

/**
 * Class DomainEventsMiddleware.
 */
class DomainEventsMiddleware implements Middleware
{
    /**
     * @var EventStore
     *
     * Event Store
     */
    private $eventStore;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param EventStore     $eventStore
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        EventStore $eventStore,
        EventPublisher $eventPublisher
    ) {
        $this->eventStore = $eventStore;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $eventSubscriber = new CollectInMemoryDomainEventSubscriber();
        $this
            ->eventPublisher
            ->subscribe($eventSubscriber);

        $result = $next($command);

        $previousEvent = null;
        foreach ($eventSubscriber->getEvents() as $event) {
            $this->eventStore->append($event, $previousEvent);
            $previousEvent = $event;
        }

        return $result;
    }
}
