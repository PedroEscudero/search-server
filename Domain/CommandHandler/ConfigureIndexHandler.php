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

use Apisearch\Server\Domain\Command\ConfigureIndex;
use Apisearch\Server\Domain\Event\IndexWasConfigured;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class ConfigIndexHandler.
 */
class ConfigureIndexHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Configure the index.
     *
     * @param ConfigureIndex $configureIndex
     */
    public function handle(ConfigureIndex $configureIndex)
    {
        $repositoryReference = $configureIndex->getRepositoryReference();
        $config = $configureIndex->getConfig();

        $this
            ->repository
            ->setRepositoryReference($repositoryReference);

        $this
            ->repository
            ->configureIndex($config);

        $this
            ->eventPublisher
            ->publish(new IndexWasConfigured($config));
    }
}
