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

namespace Puntmig\Search\Server\Controller;

use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Elastica\Repository\EventRepository;

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
            ->setAppId($query->get(HttpRepository::APP_ID_FIELD));
    }
}
