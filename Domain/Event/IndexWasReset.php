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

/**
 * Class IndexWasReset.
 */
class IndexWasReset extends DomainEvent
{
    /**
     * ItemsWasIndexed constructor.
     */
    public function __construct()
    {
        $this->setNow();
    }

    /**
     * Payload to array.
     *
     * @return array
     */
    public function payloadToArray(): array
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
