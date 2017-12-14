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

use Apisearch\Server\Domain\CommandWithRepositoryReference;
use Apisearch\Server\Domain\Event\DomainEvent;
use League\Tactician\Middleware;

/**
 * Class IgnoreDomainEventsMiddleware.
 */
class IgnoreDomainEventsMiddleware extends DomainEventsMiddleware implements Middleware
{
    /**
     * Process events.
     *
     * @param CommandWithRepositoryReference $command
     * @param DomainEvent                    $event
     */
    public function processEvent(
        CommandWithRepositoryReference $command,
        DomainEvent $event
    ) {
        // Silent pass
    }
}
