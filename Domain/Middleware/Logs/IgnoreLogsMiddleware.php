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
use League\Tactician\Middleware;

/**
 * Class IgnoreLogsMiddleware.
 */
class IgnoreLogsMiddleware extends LogsMiddleware implements Middleware
{
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
        // Silent pass
    }
}
