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

namespace Mmoreram\SearchBundle\Result;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

use Mmoreram\SearchBundle\Query\Filter;

/**
 * Class Aggregation.
 */
class Aggregation implements IteratorAggregate
{
    /**
     * @var Counter[]
     *
     * Counters
     */
    private $counters = [];

    /**
     * @var string
     *
     * Aggregation type
     */
    private $type;

    /**
     * @var int
     *
     * Total elements
     */
    private $totalElements;

    /**
     * @var int
     *
     * Lowest level
     */
    private $lowestLevel;

    /**
     * Aggregation constructor.
     *
     * @param string $type
     * @param int    $totalElements
     */
    public function __construct(
        string $type,
        int $totalElements
    ) {
        $this->type = $type;
        $this->totalElements = $totalElements;
    }

    /**
     * Add aggregation counter.
     *
     * @param string $name
     * @param int    $counter
     * @param array  $activeElements
     */
    public function addCounter(
        string $name,
        int $counter,
        array $activeElements
    ) {
        $counter = Counter::createByActiveElements(
            $name,
            $counter,
            $activeElements
        );

        if (
            $this->type === Filter::MUST_ALL &&
            $counter->isUsed()
        ) {
            return;
        }

        $this->counters[$counter->getId()] = $counter;
        $this->lowestLevel = is_null($this->lowestLevel)
            ? $counter->getLevel()
            : min($this->lowestLevel, $counter->getLevel());
    }

    /**
     * Get counters.
     *
     * @return Counter[]
     */
    public function getCounters(): array
    {
        return $this->counters;
    }

    /**
     * Return if the aggregation belongs to a filter.
     *
     * @return bool
     */
    public function isFilter(): bool
    {
        return $this->type === Filter::MUST_ALL;
    }

    /**
     * Get counter.
     *
     * @param string $name
     *
     * @return null|Counter
     */
    public function getCounter(string $name) : ? Counter
    {
        return $this->counters[$name] ?? null;
    }

    /**
     * Clean results by level and remove all levels higher than the lowest.
     */
    public function cleanCountersByLevel()
    {
        foreach ($this->counters as $pos => $counter) {
            if ($counter->getLevel() !== $this->lowestLevel) {
                unset($this->counters[$pos]);
            }
        }
    }

    /**
     * Retrieve an external iterator.
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *                     <b>Traversable</b>
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->counters);
    }
}
