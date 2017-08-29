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

use Puntmig\Search\Server\Domain\Command\Index as IndexCommand;
use Puntmig\Search\Server\Domain\Event\ItemsWereIndexed;
use Puntmig\Search\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class IndexHandler.
 */
class IndexHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the index.
     *
     * @param IndexCommand $indexCommand
     */
    public function handle(IndexCommand $indexCommand)
    {
        $key = $indexCommand->getKey();
        $items = $indexCommand->getItems();

        $this
            ->repository
            ->setKey($key);

        $this
            ->repository
            ->addItems($items);

        $this
            ->eventPublisher
            ->publish(new ItemsWereIndexed(
                $key,
                $items
            ));
    }
}
