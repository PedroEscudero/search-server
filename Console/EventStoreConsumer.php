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

namespace Apisearch\Server\Console;

use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Event\DomainEvent;
use Apisearch\Server\Domain\Event\EventStore;
use RSQueue\Command\ConsumerCommand;
use RSQueue\Services\Consumer;
use RSQueue\Services\Publisher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * File header placeholder.
 */
class EventStoreConsumer extends ConsumerCommand
{
    /**
     * @var EventStore
     *
     * Event store
     */
    private $eventStore;

    /**
     * @var Publisher
     *
     * Publisher
     */
    private $publisher;

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer   $consumer
     * @param EventStore $eventStore
     * @param Publisher  $publisher
     */
    public function __construct(
        Consumer $consumer,
        EventStore $eventStore,
        Publisher $publisher
    ) {
        parent::__construct($consumer);

        $this->eventStore = $eventStore;
        $this->publisher = $publisher;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('apisearch-server:domain-events-consumer');
    }

    /**
     * Definition method.
     *
     * All RSQueue commands must implements its own define() method
     * This method will subscribe command to desired queues
     * with their respective methods
     */
    public function define()
    {
        $this->addQueue('apisearch:server:domain-events', 'persistDomainEvent');
    }

    /**
     * Persist domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $data
     */
    protected function persistDomainEvent(
        InputInterface $input,
        OutputInterface $output,
        array $data
    ) {
        $this
            ->eventStore
            ->setRepositoryReference(
                RepositoryReference::create(
                    $data['app_id'],
                    $data['index_id']
                )
            );

        $domainEvent = DomainEvent::fromArray($data['event']);

        $this
            ->eventStore
            ->append($domainEvent);

        $this->publishExtendedDomainEvent(
            $data['app_id'],
            $data['index_id'],
            $domainEvent
        );

        $this->publishReducedDomainEvent(
            $data['app_id'],
            $data['index_id'],
            $domainEvent
        );
    }

    /**
     * Publish the event into the extended events queue.
     *
     * @param string      $appId
     * @param string      $indexId
     * @param DomainEvent $domainEvent
     */
    private function publishExtendedDomainEvent(
        string $appId,
        string $indexId,
        DomainEvent $domainEvent
    ) {
        $this
            ->publisher
            ->publish('apisearch:domain-events:extended', [
                'app_id' => $appId,
                'index_id' => $indexId,
                'event' => $domainEvent->toArray(),
            ]);
    }

    /**
     * Publish the event into the reduced events queue.
     *
     * @param string      $appId
     * @param string      $indexId
     * @param DomainEvent $domainEvent
     */
    private function publishReducedDomainEvent(
        string $appId,
        string $indexId,
        DomainEvent $domainEvent
    ) {
        $this
            ->publisher
            ->publish('apisearch:domain-events:reduced', [
                'app_id' => $appId,
                'index_id' => $indexId,
                'event' => $domainEvent->toReducedArray(),
            ]);
    }
}
