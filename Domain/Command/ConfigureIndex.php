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
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Server\Domain\WriteCommand;

/**
 * Class ConfigureIndex.
 */
class ConfigureIndex implements WithRepositoryReference, WriteCommand, LoggableCommand
{
    use WithRepositoryReferenceTrait;

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
     * @param Config              $config
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Config $config
    ) {
        $this->repositoryReference = $repositoryReference;
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
