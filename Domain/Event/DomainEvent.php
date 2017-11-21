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
    abstract public function payloadToArray(): array;

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
        $namespace = 'Puntmig\Search\Server\Domain\Event\\'.$data['type'];

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
            'type' => str_replace('Puntmig\Search\Server\Domain\Event\\', '', get_class($this)),
            'occurred_on' => $this->occurredOn(),
            'payload' => json_encode($this->payloadToArray()),
        ];
    }
}
