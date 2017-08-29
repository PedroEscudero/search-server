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

namespace Puntmig\Search\Server\Domain;

use Puntmig\Search\Repository\Repository;
use Puntmig\Search\Server\Domain\Event\EventPublisher;

/**
 * Class WithRepositoryAndEventPublisher.
 */
abstract class WithRepositoryAndEventPublisher
{
    /**
     * @var Repository
     *
     * Repository
     */
    protected $repository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    protected $eventPublisher;

    /**
     * QueryHandler constructor.
     *
     * @param Repository     $repository
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        Repository $repository,
        EventPublisher $eventPublisher
    ) {
        $this->repository = $repository;
        $this->eventPublisher = $eventPublisher;
    }
}
