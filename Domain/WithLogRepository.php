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

namespace Apisearch\Server\Domain;

use Apisearch\Log\LogRepository;

/**
 * Class WithLogRepository.
 */
abstract class WithLogRepository
{
    /**
     * @var LogRepository
     *
     * Log repository
     */
    protected $logRepository;

    /**
     * ListEventsHandler constructor.
     *
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }
}
