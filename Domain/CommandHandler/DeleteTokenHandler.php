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

use Apisearch\Server\Domain\Command\DeleteToken;
use Apisearch\Server\Domain\Event\TokenWasDeleted;
use Apisearch\Server\Domain\WithAppRepositoryAndEventPublisher;

/**
 * Class DeleteTokenHandler.
 */
class DeleteTokenHandler extends WithAppRepositoryAndEventPublisher
{
    /**
     * Delete token.
     *
     * @param DeleteToken $deleteToken
     */
    public function handle(DeleteToken $deleteToken)
    {
        $repositoryReference = $deleteToken->getRepositoryReference();
        $tokenUUID = $deleteToken->getTokenUUID();

        $this
            ->appRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->appRepository
            ->deleteToken($tokenUUID);

        $this
            ->eventPublisher
            ->publish(new TokenWasDeleted($tokenUUID));
    }
}
