<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Model;

/**
 * Class Aggregation.
 */
class Aggregation
{
    /**
     * @var int[]
     *
     * Counters
     */
    private $counters = [];

    /**
     * @var int
     *
     * Total elements
     */
    private $totalElements;

    /**
     * Aggregation constructor.
     *
     * @param int $totalElements
     */
    public function __construct(int $totalElements)
    {
        $this->totalElements = $totalElements;
    }

    /**
     * Add aggregation counter.
     *
     * @param string $name
     * @param int    $counter
     */
    public function addCounter(
        string $name,
        int $counter
    ) {
        $this->counters[$name] = $counter;
    }

    /**
     * Get counters.
     *
     * @return int[]
     */
    public function getCounters(): array
    {
        return $this->counters;
    }

    /**
     * Get counter.
     *
     * @param string $name
     *
     * @return int
     */
    public function getCounter(string $name) : int
    {
        return $this->counters[$name] ?? 0;
    }
}
