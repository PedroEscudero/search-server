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

namespace Apisearch\Server\Elastica\Middleware;

use Apisearch\Server\Domain\Event\CollectInMemoryDomainEventSubscriber;
use Apisearch\Server\Domain\Event\DomainEvent;
use Apisearch\Server\Domain\Event\EventPublisher;
use Carbon\Carbon;
use Elastica\Client;
use Exception;
use League\Tactician\Middleware;
use Monolog\Handler\ElasticSearchHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Class LogAllDomainEventsToElasticaMiddleware.
 */
class LogAllDomainEventsToElasticaMiddleware implements Middleware
{
    /**
     * @var Client
     *
     * Elastica client
     */
    private $client;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * @var Logger
     *
     * Logger
     */
    private $logger;

    /**
     * LogAllDomainEventsMiddleware constructor.
     *
     * @param Client         $client
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        Client $client,
        EventPublisher $eventPublisher
    ) {
        $this->client = $client;
        $this->eventPublisher = $eventPublisher;
        $this->logger = new Logger(
            'events',
            [],
            [
                new MemoryUsageProcessor(),
                new MemoryPeakUsageProcessor(),
            ]
        );
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
            $this->logDomainEvent($event);
        }

        return $result;
    }

    /**
     * Log domain event.
     *
     * @param DomainEvent $event
     */
    private function logDomainEvent(DomainEvent $event)
    {
        $type = explode('\\', get_class($event));
        $this->logger->setHandlers([
            new ElasticSearchHandler(
                $this->client,
                [
                    'index' => 'events',
                    'type' => end($type),
                ]
            ),
        ]);

        $context = $event->toArray();
        $context['occurred_on'] = $event->occurredOn();
        $context['occurred_on_in_atom'] = Carbon::createFromTimestampUTC($event->occurredOn())->toAtomString();

        try {
            $this
                ->logger
                ->info(
                    get_class($event),
                    $context
                );
        } catch (Exception $e) {
            // Silent pass
            die($e->getMessage());
        }
    }
}
