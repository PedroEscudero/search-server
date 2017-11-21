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
use RSQueue\Services\Producer as QueueProducer;

use Puntmig\Search\Server\Domain\Event\CollectInMemoryDomainEventSubscriber;
use Puntmig\Search\Server\Domain\Event\EventPublisher;

/**
 * Class DomainEventsMiddleware.
 */
class DomainEventsMiddleware implements Middleware
{
    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * @var QueueProducer
     *
     * Queue producer
     */
    private $queueProducer;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param EventPublisher $eventPublisher
     * @param QueueProducer  $queueProducer
     */
    public function __construct(
        EventPublisher $eventPublisher,
        QueueProducer $queueProducer
    ) {
        $this->eventPublisher = $eventPublisher;
        $this->queueProducer = $queueProducer;
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

        foreach ($eventSubscriber->getEvents() as $event) {
            $this
                ->queueProducer
                ->produce(
                    'search-server:domain-events',
                    [
                        'app_id' => $command->getAppId(),
                        'event' => $event->toArray(),
                    ]
                );
        }

        return $result;
    }
}
