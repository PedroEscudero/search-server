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

namespace Apisearch\Server\Domain\Middleware\Logs;

use Apisearch\Log\Log;
use Apisearch\Log\LogRepository;
use Apisearch\Repository\WithRepositoryReference;
use League\Tactician\Middleware;

/**
 * Class InlineLogsMiddleware.
 */
class InlineLogsMiddleware extends LogsMiddleware implements Middleware
{
    /**
     * @var LogRepository
     *
     * Log repository
     */
    private $logRepository;

    /**
     * DomainEventsMiddleware constructor.
     *
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Process log.
     *
     * @param WithRepositoryReference $command
     * @param Log                     $log
     */
    public function processLog(
        WithRepositoryReference $command,
        Log $log
    ) {
        $this
            ->logRepository
            ->setRepositoryReference($command->getRepositoryReference());

        $this
            ->logRepository
            ->save($log);
    }
}
