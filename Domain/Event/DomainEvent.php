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

namespace Apisearch\Server\Domain\Event;

use Apisearch\Server\Domain\Now;
use Carbon\Carbon;
use Exception;
use ReflectionClass;

/**
 * Abstract class DomainEvent.
 */
abstract class DomainEvent
{
    /**
     * @var int
     *
     * Occurred on
     */
    private $occurredOn;

    /**
     * @var Carbon;
     *
     * Now
     */
    private $now;

    /**
     * Mark occurred on as now.
     */
    protected function setNow()
    {
        $this->now = Carbon::now('UTC');
        $this->occurredOn = Now::epochTimeWithMicroseconds($this->now);
    }

    /**
     * Return when event occurred.
     *
     * @return int
     */
    public function occurredOn(): int
    {
        return $this->occurredOn;
    }

    /**
     * Return specific occurred_on ranges.
     *
     * @return int[]
     */
    public function occurredOnRanges(): array
    {
        return [
            'occurred_on_day' => $this->now->startOfDay()->timestamp,
            'occurred_on_week' => $this->now->startOfWeek()->timestamp,
            'occurred_on_month' => $this->now->startOfMonth()->timestamp,
            'occurred_on_year' => $this->now->startOfYear()->timestamp,
        ];
    }

    /**
     * Create by plain values.
     *
     * @param int    $occurredOn
     * @param string $payload
     *
     * @return static
     */
    public static function createByPlainValues(
        int $occurredOn,
        string $payload
    ) {
        $reflector = new ReflectionClass(static::class);
        $instance = $reflector->newInstanceArgs(static::stringToPayload($payload));
        $instance->occurredOn = $occurredOn;

        return $instance;
    }

    /**
     * Payload transformation.
     */

    /**
     * Payload to array.
     *
     * @return array
     */
    public function payloadToArray(): array
    {
        return array_merge(
            $this->readableOnlyToArray(),
            $this->indexableToArray()
        );
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    abstract public function readableOnlyToArray(): array;

    /**
     * Indexable to array.
     *
     * @return array
     */
    abstract public function indexableToArray(): array;

    /**
     * To payload.
     *
     * @param string $data
     *
     * @return array
     *
     * @throws Exception
     */
    public static function stringToPayload(string $data): array
    {
        throw new Exception('Your domain event MUST implement the method fromJson');
    }

    /**
     * From array.
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function fromArray(array $data)
    {
        $namespace = 'Apisearch\Server\Domain\Event\\'.$data['type'];

        return $namespace::createByPlainValues(
            $data['occurred_on'],
            $data['payload']
        );
    }

    /**
     * To plan values.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => str_replace('Apisearch\Server\Domain\Event\\', '', get_class($this)),
            'occurred_on' => $this->occurredOn(),
            'payload' => json_encode($this->payloadToArray()),
        ];
    }

    /**
     * To plan values with only reduced values.
     *
     * @return array
     */
    public function toReducedArray(): array
    {
        return [
            'type' => str_replace('Apisearch\Server\Domain\Event\\', '', get_class($this)),
            'occurred_on' => $this->occurredOn(),
            'payload' => json_encode($this->indexableToArray()),
        ];
    }
}
