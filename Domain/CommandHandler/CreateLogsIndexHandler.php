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

use Apisearch\Server\Domain\Command\CreateLogsIndex;
use Apisearch\Server\Domain\WithLogRepository;

/**
 * Class CreateLogsIndexHandler.
 */
class CreateLogsIndexHandler extends WithLogRepository
{
    /**
     * Create the events index.
     *
     * @param CreateLogsIndex $createLogsIndex
     */
    public function handle(CreateLogsIndex $createLogsIndex)
    {
        $repositoryReference = $createLogsIndex->getRepositoryReference();

        $this
            ->logRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->logRepository
            ->createIndex();
    }
}
