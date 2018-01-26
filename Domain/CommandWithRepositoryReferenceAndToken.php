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

namespace Apisearch\Server\Domain;

use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Repository\WithTokenTrait;
use Apisearch\Token\Token;

/**
 * Class CommandWithRepositoryReferenceAndToken.
 */
class CommandWithRepositoryReferenceAndToken implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;
    use WithTokenTrait;

    /**
     * ResetCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token $token
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->token = $token;
    }
}
