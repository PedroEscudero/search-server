<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Domain\Query;

use Puntmig\Search\Server\Domain\WithAppId;

/**
 * Class StatsEvents.
 */
class StatsEvents extends WithAppId
{
    /**
     * @var int|null
     *
     * From
     */
    private $from;

    /**
     * @var int|null
     *
     * To
     */
    private $to;

    /**
     * DeleteCommand constructor.
     *
     * @param string   $appId
     * @param int|null $from
     * @param int|null $to
     */
    public function __construct(
        string $appId,
        ?int $from,
        ?int $to
    ) {
        $this->appId = $appId;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get From.
     *
     * @return int|null
     */
    public function getFrom(): ? int
    {
        return $this->from;
    }

    /**
     * Get To.
     *
     * @return int|null
     */
    public function getTo(): ? int
    {
        return $this->to;
    }
}
