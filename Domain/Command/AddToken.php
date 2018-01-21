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
use Apisearch\Server\Domain\CommandWithRepositoryReference;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Token\Token;

/**
 * Class AddToken.
 */
class AddToken extends CommandWithRepositoryReference implements LoggableCommand
{
    /**
     * @var Token
     *
     * Token
     */
    private $token;

    /**
     * AddToken constructor.
     *
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token $token
    ) {
        parent::__construct($repositoryReference);

        $this->token = $token;
    }

    /**
     * Get Token.
     *
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }
}
