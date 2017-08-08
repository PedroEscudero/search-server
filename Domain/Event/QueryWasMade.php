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

use Puntmig\Search\Result\Result;

/**
 * Class QueryWasMade.
 */
class QueryWasMade extends DomainEvent
{
    /**
     * @var Result
     *
     * Result
     */
    private $result;

    /**
     * ItemsWasIndexed constructor.
     *
     * @param string $key
     * @param Result $result
     */
    public function __construct(
        string $key,
        Result $result
    ) {
        $this->key = $key;
        $this->result = $result;
        $this->setNow();
    }

    /**
     * To payload.
     *
     * @return string
     */
    public function toPayload() : string
    {
        return json_encode(
            $this
                ->result
                ->toArray()
        );
    }

    /**
     * To payload.
     *
     * @param string $payload
     *
     * @return array
     */
    public static function fromPayload(string $payload) : array
    {
        return [
            Result::createFromArray($payload),
        ];
    }
}
