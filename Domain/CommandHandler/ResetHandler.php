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

namespace Puntmig\Search\Server\Domain\CommandHandler;

use Puntmig\Search\Event\EventRepository;
use Puntmig\Search\Repository\Repository;
use Puntmig\Search\Server\Domain\Command\Reset as ResetCommand;
use Puntmig\Search\Server\Domain\Event\EventPublisher;
use Puntmig\Search\Server\Domain\Event\IndexWasReset;
use Puntmig\Search\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class ResetHandler.
 */
class ResetHandler extends WithRepositoryAndEventPublisher
{
    /**
     * @var EventRepository
     *
     * Event repository
     */
    private $eventRepository;

    /**
     * QueryHandler constructor.
     *
     * @param Repository      $repository
     * @param EventPublisher  $eventPublisher
     * @param EventRepository $eventRepository
     */
    public function __construct(
        Repository $repository,
        EventPublisher $eventPublisher,
        EventRepository $eventRepository
    ) {
        parent::__construct(
            $repository,
            $eventPublisher
        );

        $this->eventRepository = $eventRepository;
    }

    /**
     * Reset the index.
     *
     * @param ResetCommand $resetCommand
     */
    public function handle(ResetCommand $resetCommand)
    {
        $appId = $resetCommand->getAppId();
        $language = $resetCommand->getLanguage();

        $this
            ->repository
            ->setAppId($appId);

        $this
            ->repository
            ->reset($language);

        $this
            ->eventRepository
            ->setAppId($appId);

        $this
            ->eventRepository
            ->createRepository();

        $this
            ->eventPublisher
            ->publish(new IndexWasReset($language));
    }
}
