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

namespace Puntmig\Search\Server\Domain\Event;

use Carbon\Carbon;
use Exception;
use ReflectionClass;

/**
 * Abstract class DomainEvent.
 */
abstract class DomainEvent
{
    /**
     * @var string
     *
     * Key
     */
    protected $key;

    /**
     * @var int
     *
     * Occurred on
     */
    private $occurredOn;

    /**
     * Mark occurred on as now.
     */
    protected function setNow()
    {
        $this->occurredOn = Carbon::today('UTC')->getTimestamp();
    }

    /**
     * Return when event occurred.
     *
     * @return int
     */
    public function occurredOn() : int
    {
        return $this->occurredOn;
    }

    /**
     * Get Key.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * Create by plain values.
     *
     * @param string $key
     * @param int    $occurredOn
     * @param string $payload
     *
     * @return static
     */
    public static function createByPlainValues(
        string $key,
        int $occurredOn,
        string $payload
    ) {
        $reflector = new ReflectionClass(static::class);
        $instance = $reflector->newInstanceArgs(array_merge(
            [$key],
            static::fromPayload($payload)
        ));
        $instance->occurredOn = $occurredOn;

        return $instance;
    }

    /**
     * Payload transformation.
     */

    /**
     * To payload.
     *
     * @return string
     */
    abstract public function toPayload() : string;

    /**
     * To payload.
     *
     * @param string $payload
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function fromPayload(string $payload)
    {
        throw new Exception('Your domain event MUST implement the method fromJson');
    }
}
