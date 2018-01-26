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

use Apisearch\Server\Domain\Command\AddInteraction;
use Apisearch\Server\Domain\Event\InteractionWasAdded;
use Apisearch\Server\Domain\WithUserRepositoryAndEventPublisher;

/**
 * Class AddInteractionHandler.
 */
class AddInteractionHandler extends WithUserRepositoryAndEventPublisher
{
    /**
     * Add interaction.
     *
     * @param AddInteraction $addInteraction
     */
    public function handle(AddInteraction $addInteraction)
    {
        $repositoryReference = $addInteraction->getRepositoryReference();
        $interaction = $addInteraction->getInteraction();

        $this
            ->userRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->userRepository
            ->addInteraction($interaction);

        $this
            ->eventPublisher
            ->publish(new InteractionWasAdded($interaction));
    }
}
