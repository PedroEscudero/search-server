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

namespace Apisearch\Server\Domain\Repository\LogRepository;

use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Log\Log;
use Apisearch\Log\LogRepository as BaseLogRepository;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryWithCredentials;
use Apisearch\Result\Logs;
use Apisearch\Server\Domain\Repository\WithRepositories;

/**
 * Class Repository.
 */
class Repository extends RepositoryWithCredentials implements BaseLogRepository
{
    use WithRepositories;

    /**
     * Create an index.
     *
     * @throws ResourceExistsException
     */
    public function createIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->createIndex();
    }

    /**
     * Delete an index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->deleteIndex();
    }

    /**
     * Save log.
     *
     * @param Log $log
     *
     * @throws ResourceNotAvailableException
     */
    public function save(Log $log)
    {
        $this
            ->getRepository(IndexRepository::class)
            ->addLog($log);
    }

    /**
     * Query over logs.
     *
     * @param Query    $query
     * @param int|null $from
     * @param int|null $to
     *
     * @return Logs
     *
     * @throws ResourceNotAvailableException
     */
    public function query(
        Query $query,
        ? int $from = null,
        ? int $to = null
    ): Logs {
        return $this
            ->getRepository(QueryRepository::class)
            ->query(
                $query,
                $from,
                $to
            );
    }
}
