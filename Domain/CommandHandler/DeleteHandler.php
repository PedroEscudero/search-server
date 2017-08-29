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

use Puntmig\Search\Server\Domain\Command\Delete as DeleteCommand;
use Puntmig\Search\Server\Domain\Event\ItemsWereDeleted;
use Puntmig\Search\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class DeleteHandler.
 */
class DeleteHandler extends WithRepositoryAndEventPublisher
{
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
            ->repository
            ->setKey($key);

        $this
            ->repository
            ->deleteItems($itemsUUID);

        $this
            ->eventPublisher
            ->publish(new ItemsWereDeleted(
                $key,
                $itemsUUID
            ));
    }
}
