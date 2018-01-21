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

namespace Apisearch\Server\Domain;

use Apisearch\App\AppRepository;
use Apisearch\Server\Domain\Event\EventPublisher;

/**
 * Class WithAppRepositoryAndEventPublisher.
 */
abstract class WithAppRepositoryAndEventPublisher
{
    /**
     * @var AppRepository
     *
     * App Repository
     */
    protected $appRepository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    protected $eventPublisher;

    /**
     * QueryHandler constructor.
     *
     * @param AppRepository  $appRepository
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        AppRepository $appRepository,
        EventPublisher $eventPublisher
    ) {
        $this->appRepository = $appRepository;
        $this->eventPublisher = $eventPublisher;
    }
}
