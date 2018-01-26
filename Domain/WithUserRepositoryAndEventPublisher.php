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

use Apisearch\Server\Domain\Event\EventPublisher;
use Apisearch\User\UserRepository;

/**
 * Class WithUserRepositoryAndEventPublisher.
 */
abstract class WithUserRepositoryAndEventPublisher
{
    /**
     * @var UserRepository
     *
     * User Repository
     */
    protected $userRepository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    protected $eventPublisher;

    /**
     * QueryHandler constructor.
     *
     * @param UserRepository $userRepository
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        UserRepository $userRepository,
        EventPublisher $eventPublisher
    ) {
        $this->userRepository = $userRepository;
        $this->eventPublisher = $eventPublisher;
    }
}
