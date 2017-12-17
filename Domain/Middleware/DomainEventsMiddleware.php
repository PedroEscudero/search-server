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

namespace Apisearch\Server\Domain\Middleware;

use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Server\Domain\Event\CollectInMemoryDomainEventSubscriber;
use Apisearch\Server\Domain\Event\DomainEvent;
use Apisearch\Server\Domain\Event\EventPublisher;

/**
 * Class DomainEventsMiddleware.
 */
abstract class DomainEventsMiddleware
{
    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param EventPublisher $eventPublisher
     */
    public function __construct(EventPublisher $eventPublisher)
    {
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param WithRepositoryReference $command
     * @param callable                $next
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

        foreach ($eventSubscriber->getEvents() as $event) {
            $this->processEvent(
                $command,
                $event
            );
        }

        return $result;
    }

    /**
     * Process events.
     *
     * @param WithRepositoryReference $command
     * @param DomainEvent             $event
     */
    abstract public function processEvent(
        WithRepositoryReference $command,
        DomainEvent $event
    );
}
