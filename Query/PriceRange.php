<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Mmoreram\SearchBundle\Query;

/**
 * Class PriceRange
 */
class PriceRange
{
    /**
     * @var int
     *
     * free
     */
    const FREE = 0;

    /**
     * @var int
     *
     * Infinite
     */
    const INFINITE = -1;

    /**
     * @var int
     *
     * From
     */
    private $from;

    /**
     * @var int
     *
     * To
     */
    private $to;

    /**
     * FromToPrice constructor.
     *
     * @param int $from
     * @param int $to
     */
    public function __construct(
        int $from,
        int $to
    )
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get from
     *
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * Get to
     *
     * @return int
     */
    public function getTo(): int
    {
        return $this->to;
    }
}