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

use Apisearch\Server\Domain\Command\ResetIndex;
use Apisearch\Server\Domain\Event\IndexWasReset;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class ResetIndexHandler.
 */
class ResetIndexHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the index.
     *
     * @param ResetIndex $resetIndex
     */
    public function handle(ResetIndex $resetIndex)
    {
        $repositoryReference = $resetIndex->getRepositoryReference();

        $this
            ->repository
            ->setRepositoryReference($repositoryReference);

        $this
            ->repository
            ->resetIndex();

        $this
            ->eventPublisher
            ->publish(new IndexWasReset());
    }
}
