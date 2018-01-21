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

namespace Apisearch\Server\Domain\Repository\AppRepository;

use Apisearch\App\AppRepository as BaseRepository;
use Apisearch\Repository\RepositoryWithCredentials;
use Apisearch\Server\Domain\Repository\WithRepositories;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class Repository.
 */
class Repository extends RepositoryWithCredentials implements BaseRepository
{
    use WithRepositories;

    /**
     * Add token.
     *
     * @param Token $token
     */
    public function addToken(Token $token)
    {
        $this
            ->getRepository(TokenRepository::class)
            ->addToken($token);
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     */
    public function deleteToken(TokenUUID $tokenUUID)
    {
        $this
            ->getRepository(TokenRepository::class)
            ->deleteToken($tokenUUID);
    }
}
