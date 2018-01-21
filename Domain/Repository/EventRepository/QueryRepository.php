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

namespace Apisearch\Server\Domain\Repository\EventRepository;

use Apisearch\Query\Query;
use Apisearch\Result\Events;

/**
 * Interface QueryRepository.
 */
interface QueryRepository
{
    /**
     * Make a query and return an Events instance.
     *
     * @param Query    $query
     * @param int|null $from
     * @param int|null $to
     *
     * @return Events
     */
    public function query(
        Query $query,
        ?int $from,
        ?int $to
    ): Events;
}
