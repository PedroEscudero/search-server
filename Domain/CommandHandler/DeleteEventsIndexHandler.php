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

use Apisearch\Server\Domain\Command\DeleteEventsIndex;
use Apisearch\Server\Domain\WithEventRepository;

/**
 * Class DeleteEventsIndexHandler.
 */
class DeleteEventsIndexHandler extends WithEventRepository
{
    /**
     * Deletes de events index
     *
     * @param DeleteEventsIndex $deleteEventsIndex
     */
    public function handle(DeleteEventsIndex $deleteEventsIndex)
    {
        $repositoryReference = $deleteEventsIndex->getRepositoryReference();

        $this
            ->eventRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->eventRepository
            ->deleteIndex();
    }
}
