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

use Apisearch\Config\Config;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Repository\WithTokenTrait;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Server\Domain\WriteCommand;
use Apisearch\Token\Token;

/**
 * Class ConfigureIndex.
 */
class ConfigureIndex implements WithRepositoryReference, WriteCommand, LoggableCommand
{
    use WithRepositoryReferenceTrait;
    use WithTokenTrait;

    /**
     * @var Config
     *
     * Query
     */
    private $config;

    /**
     * DeleteCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param Config              $config
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token              $token,
        Config $config
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->token = $token;
        $this->config = $config;
    }

    /**
     * Get Query.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
