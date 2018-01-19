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
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Server\Domain\Now;
use Exception;
use Ramsey\Uuid\Uuid;

/**
 * Class LogsMiddleware.
 */
abstract class LogsMiddleware
{
    /**
     * @param WithRepositoryReference $command
     * @param callable                $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        try {
            $result = $next($command);
        } catch (Exception $exception) {
            if ($command instanceof LoggableCommand) {
                $this->processLog(
                    $command,
                    Log::createFromArray([
                        'id' => Uuid::uuid4(),
                        'type' => Log::TYPE_FATAL,
                        'payload' => $exception->getMessage(),
                        'occurred_on' => Now::epochTimeWithMicroseconds(),
                    ])
                );
            }

            throw $exception;
        }

        return $result;
    }

    /**
     * Process log.
     *
     * @param WithRepositoryReference $command
     * @param Log                     $log
     */
    abstract public function processLog(
        WithRepositoryReference $command,
        Log $log
    );
}
