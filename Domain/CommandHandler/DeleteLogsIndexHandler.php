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

use Apisearch\Server\Domain\Command\DeleteLogsIndex;
use Apisearch\Server\Domain\WithLogRepository;

/**
 * Class DeleteLogsIndexHandler.
 */
class DeleteLogsIndexHandler extends WithLogRepository
{
    /**
     * Deletes logs index.
     *
     * @param DeleteLogsIndex $deleteLogsIndex
     */
    public function handle(DeleteLogsIndex $deleteLogsIndex)
    {
        $repositoryReference = $deleteLogsIndex->getRepositoryReference();

        $this
            ->logRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->logRepository
            ->deleteIndex();
    }
}
