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

use Apisearch\Server\Domain\Command\IndexItems;
use Apisearch\Server\Domain\Event\ItemsWereIndexed;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class IndexItemsHandler.
 */
class IndexItemsHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the index.
     *
     * @param IndexItems $indexItems
     */
    public function handle(IndexItems $indexItems)
    {
        $items = $indexItems->getItems();

        $this
            ->repository
            ->setRepositoryReference($indexItems->getRepositoryReference());

        $this
            ->repository
            ->addItems($items);

        $this
            ->eventPublisher
            ->publish(new ItemsWereIndexed($items));
    }
}
