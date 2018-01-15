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

namespace Apisearch\Server\Domain\QueryHandler;

use Apisearch\Result\Logs;
use Apisearch\Server\Domain\Query\QueryLogs;
use Apisearch\Server\Domain\WithLogRepository;

/**
 * Class QueryLogsHandler.
 */
class QueryLogsHandler extends WithLogRepository
{
    /**
     * Query events.
     *
     * @param QueryLogs $queryLogs
     *
     * @return Logs
     */
    public function handle(QueryLogs $queryLogs): Logs
    {
        $this
            ->logRepository
            ->setRepositoryReference($queryLogs->getRepositoryReference());

        return $this
            ->logRepository
            ->query(
                $queryLogs->getQuery(),
                $queryLogs->getFrom(),
                $queryLogs->getTo()
            );
    }
}
