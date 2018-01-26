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

use Apisearch\Server\Domain\Command\DeleteAllInteractions;
use Apisearch\Server\Domain\WithUserRepositoryAndEventPublisher;

/**
 * Class DeleteAllInteractionsHandler.
 */
class DeleteAllInteractionsHandler extends WithUserRepositoryAndEventPublisher
{
    /**
     * Delete all the interactions.
     *
     * @param DeleteAllInteractions $deleteAllInteractions
     */
    public function handle(DeleteAllInteractions $deleteAllInteractions)
    {
        $repositoryReference = $deleteAllInteractions->getRepositoryReference();

        $this
            ->userRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->userRepository
            ->deleteAllInteractions();
    }
}
