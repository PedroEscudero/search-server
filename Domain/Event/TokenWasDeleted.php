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

use Apisearch\Token\TokenUUID;

/**
 * Class TokenWasDeleted.
 */
class TokenWasDeleted extends DomainEvent
{
    /**
     * @var TokenUUID
     *
     * Token UUID
     */
    private $tokenUUID;

    /**
     * ItemsWasIndexed constructor.
     */
    public function __construct(TokenUUID $tokenUUID)
    {
        $this->setNow();
        $this->tokenUUID = $tokenUUID;
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function readableOnlyToArray(): array
    {
        return [
            'token' => json_encode($this->tokenUUID->toArray()),
        ];
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function indexableToArray(): array
    {
        return [];
    }

    /**
     * To payload.
     *
     * @param string $data
     *
     * @return array
     */
    public static function stringToPayload(string $data): array
    {
        return [];
    }
}
