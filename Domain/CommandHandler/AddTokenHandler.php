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

use Apisearch\Server\Domain\Command\AddToken;
use Apisearch\Server\Domain\Event\TokenWasAdded;
use Apisearch\Server\Domain\WithAppRepositoryAndEventPublisher;

/**
 * Class AddTokenHandler.
 */
class AddTokenHandler extends WithAppRepositoryAndEventPublisher
{
    /**
     * Add token.
     *
     * @param AddToken $addToken
     */
    public function handle(AddToken $addToken)
    {
        $repositoryReference = $addToken->getRepositoryReference();
        $token = $addToken->getToken();

        $this
            ->appRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->appRepository
            ->addToken($token);

        $this
            ->eventPublisher
            ->publish(new TokenWasAdded($token));
    }
}
