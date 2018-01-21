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
use Apisearch\Token\TokenUUID;

/**
 * Class DeleteToken.
 */
class DeleteToken extends CommandWithRepositoryReference implements LoggableCommand
{
    /**
     * @var TokenUUID
     *
     * Token UUID
     */
    private $tokenUUID;

    /**
     * AddToken constructor.
     *
     *
     * @param RepositoryReference $repositoryReference
     * @param TokenUUID           $tokenUUID
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        TokenUUID $tokenUUID
    ) {
        parent::__construct($repositoryReference);

        $this->tokenUUID = $tokenUUID;
    }

    /**
     * Get Token.
     *
     * @return TokenUUID
     */
    public function getTokenUUID(): TokenUUID
    {
        return $this->tokenUUID;
    }
}
