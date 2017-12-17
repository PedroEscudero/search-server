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
use Apisearch\Server\Domain\Event\DomainEvent;
use Apisearch\Server\Domain\Event\EventPublisher;
use Apisearch\Server\Domain\Event\EventStore;
use League\Tactician\Middleware;

/**
 * Class InlineDomainEventsMiddleware.
 */
class InlineDomainEventsMiddleware extends DomainEventsMiddleware implements Middleware
{
    /**
     * @var EventStore
     *
     * Event store
     */
    private $eventStore;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param EventPublisher $eventPublisher
     * @param EventStore     $eventStore
     */
    public function __construct(
        EventPublisher $eventPublisher,
        EventStore $eventStore
    ) {
        parent::__construct($eventPublisher);

        $this->eventStore = $eventStore;
    }

    /**
     * Process events.
     *
     * @param WithRepositoryReference $command
     * @param DomainEvent                    $event
     */
    public function processEvent(
        WithRepositoryReference $command,
        DomainEvent $event
    ) {
        $this
            ->eventStore
            ->append($event);
    }
}
