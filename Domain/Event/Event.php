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

/**
 * Class Event.
 */
class Event
{
    /**
     * @var int
     *
     * Id
     */
    private $id;

    /**
     * var string.
     *
     * Consistency hash
     */
    private $consistencyHash;

    /**
     * @var string
     *
     * name
     */
    private $name;

    /**
     * @var string
     *
     * Key
     */
    private $key;

    /**
     * @var string
     *
     * Payload
     */
    private $payload;

    /**
     * @var int
     *
     * Occurred on
     */
    private $occurredOn;

    /**
     * Event constructor.
     *
     * @param null|Event $previousEvent
     * @param string     $name
     * @param string     $key
     * @param string     $payload
     * @param int        $occurredOn
     */
    public function __construct(
        ? Event $previousEvent,
        string $name,
        string $key,
        string $payload,
        int $occurredOn
    ) {
        $lastEventUUID = $previousEvent instanceof self
            ? $previousEvent->getConsistencyHash()
            : '';

        $this->consistencyHash = hash('sha256', $lastEventUUID . $name . $key . $payload . $occurredOn);
        $this->name = $name;
        $this->key = $key;
        $this->payload = $payload;
        $this->occurredOn = $occurredOn;
    }

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Get ConsistencyHash.
     *
     * @return string
     */
    public function getConsistencyHash(): string
    {
        return $this->consistencyHash;
    }

    /**
     * Get Name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get Key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get Payload.
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Get OccurredOn.
     *
     * @return int
     */
    public function getOccurredOn(): int
    {
        return $this->occurredOn;
    }
}
