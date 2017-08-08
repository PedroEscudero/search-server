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

use Puntmig\Search\Server\Domain\Command\DeleteCommand;
use Puntmig\Search\Server\Domain\Event\EventPublisher;
use Puntmig\Search\Server\Domain\Event\ItemsWereDeleted;
use Puntmig\Search\Server\Domain\Repository\DeleteRepository;

/**
 * Class DeleteHandler.
 */
class DeleteHandler
{
    /**
     * @var DeleteRepository
     *
     * Delete repository
     */
    private $deleteRepository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * DeleteHandler constructor.
     *
     * @param DeleteRepository $deleteRepository
     * @param EventPublisher   $eventPublisher
     */
    public function __construct(
        DeleteRepository $deleteRepository,
        EventPublisher $eventPublisher
    ) {
        $this->deleteRepository = $deleteRepository;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * Reset the delete.
     *
     * @param DeleteCommand $deleteCommand
     */
    public function handle(DeleteCommand $deleteCommand)
    {
        $key = $deleteCommand->getKey();
        $itemsUUID = $deleteCommand->getItemsUUID();

        $this
            ->deleteRepository
            ->setKey($key);

        $this
            ->deleteRepository
            ->deleteItems($itemsUUID);

        $this
            ->eventPublisher
            ->publish(new ItemsWereDeleted(
                $key,
                $itemsUUID
            ));
    }
}
