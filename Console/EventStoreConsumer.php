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
     * ConsumerCommand constructor.
     *
     * @param Consumer   $consumer
     * @param EventStore $eventStore
     */
    public function __construct(
        Consumer $consumer,
        EventStore $eventStore
    ) {
        parent::__construct($consumer);

        $this->eventStore = $eventStore;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('server:event-store-consumer');
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
        $this->addQueue('search-server:domain-events', 'persistDomainEvent');
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
        echo 'Event ::: '.json_encode($data).PHP_EOL;

        $this
            ->eventStore
            ->setRepositoryReference(
                RepositoryReference::create(
                    $data['app_id'],
                    $data['index']
                )
            );

        $this
            ->eventStore
            ->append(DomainEvent::fromArray($data['event']));
    }
}
