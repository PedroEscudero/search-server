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

namespace Apisearch\Server\Domain\CommandHandler;

use Apisearch\Event\EventRepository;
use Apisearch\Repository\Repository;
use Apisearch\Server\Domain\Command\Reset as ResetCommand;
use Apisearch\Server\Domain\Event\EventPublisher;
use Apisearch\Server\Domain\Event\IndexWasReset;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

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
        $repositoryReference = $resetCommand->getRepositoryReference();
        $language = $resetCommand->getLanguage();

        $this
            ->repository
            ->setRepositoryReference($repositoryReference);

        $this
            ->repository
            ->reset($language);

        $this
            ->eventRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->eventRepository
            ->createRepository();

        $this
            ->eventPublisher
            ->publish(new IndexWasReset($language));
    }
}
