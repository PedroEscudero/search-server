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
        $now = Carbon::now('UTC');
        $this->occurredOn = ($now->timestamp * 1000000) + ((int) ($now->micro / 1000) * 1000);
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
     * Create by plain values.
     *
     * @param string $occurredOn
     * @param string $payload
     *
     * @return static
     */
    public static function createByPlainValues(
        string $occurredOn,
        string $payload
    ) {
        $reflector = new ReflectionClass(static::class);
        $instance = $reflector->newInstanceArgs(static::fromPayload($payload));
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
    public function toPayload(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * To payload.
     *
     * @return array
     */
    abstract public function toArray(): array;

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
