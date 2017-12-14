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

namespace Apisearch\Server\Controller;

use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Exception\InvalidKeyException;
use Apisearch\Server\Elastica\Repository\EventRepository;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Controller.
 */
abstract class Controller
{
    /**
     * @var CommandBus
     *
     * Message bus
     */
    protected $commandBus;

    /**
     * @var EventRepository
     *
     * Event repository
     */
    private $eventRepository;

    /**
     * Controller constructor.
     *
     * @param CommandBus      $commandBus
     * @param EventRepository $eventRepository
     */
    public function __construct(
        CommandBus $commandBus,
        EventRepository $eventRepository
    ) {
        $this->commandBus = $commandBus;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Configure Event Repository.
     *
     * @param Request $request
     *
     * @throws InvalidKeyException
     */
    public function configureEventRepository(Request $request)
    {
        $query = $request->query;
        $this
            ->eventRepository
            ->setRepositoryReference(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                )
            );
    }
}
