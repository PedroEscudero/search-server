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

use Apisearch\Config\ImmutableConfig;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Server\Domain\WriteCommand;
use Apisearch\Token\Token;

/**
 * Class CreateIndex.
 */
class CreateIndex extends CommandWithRepositoryReferenceAndToken implements WriteCommand, LoggableCommand
{
    /**
     * @var ImmutableConfig
     *
     * Config
     */
    private $config;

    /**
     * ResetCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param ImmutableConfig     $config
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token $token,
        ImmutableConfig $config
    ) {
        parent::__construct(
            $repositoryReference,
            $token
        );

        $this->config = $config;
    }

    /**
     * Get config.
     *
     * @return ImmutableConfig
     */
    public function getConfig(): ImmutableConfig
    {
        return $this->config;
    }
}
