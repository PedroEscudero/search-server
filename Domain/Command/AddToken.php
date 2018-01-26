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

namespace Apisearch\Server\Domain\Command;

use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Token\Token;

/**
 * Class AddToken.
 */
class AddToken extends CommandWithRepositoryReferenceAndToken implements LoggableCommand
{
    /**
     * @var Token
     *
     * Token
     */
    private $newToken;

    /**
     * AddToken constructor.
     *
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param Token               $newToken
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token $token,
        Token $newToken
    ) {
        parent::__construct(
            $repositoryReference,
            $token
        );

        $this->newToken = $newToken;
    }

    /**
     * Get new Token.
     *
     * @return Token
     */
    public function getNewToken(): Token
    {
        return $this->newToken;
    }
}
