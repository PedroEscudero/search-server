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
use Puntmig\Search\Server\Token\TokenChecker;

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
     * @var TokenChecker
     *
     * Token checker
     */
    protected $tokenChecker;

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
     * @param TokenChecker    $tokenChecker
     * @param EventRepository $eventRepository
     */
    public function __construct(
        CommandBus $commandBus,
        TokenChecker $tokenChecker,
        EventRepository $eventRepository
    ) {
        $this->commandBus = $commandBus;
        $this->tokenChecker = $tokenChecker;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Check tocket validity.
     *
     * @param Request $request
     *
     * @throws InvalidKeyException
     */
    public function checkToken(Request $request)
    {
        $query = $request->query;
        $this
            ->tokenChecker
            ->checkToken(
                $request,
                $query->get(HttpRepository::APP_ID_FIELD, ''),
                $query->get(HttpRepository::KEY_FIELD, '')
            );

        $this
            ->eventRepository
            ->setAppId($query->get(HttpRepository::APP_ID_FIELD, ''));
    }
}
