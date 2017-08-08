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

use Puntmig\Search\Model\Item;

/**
 * Class ItemsWereIndexed.
 */
class ItemsWereIndexed extends DomainEvent
{
    /**
     * @var Item[]
     *
     * Items
     */
    private $items;

    /**
     * ItemsWasIndexed constructor.
     *
     * @param string $key
     * @param Item[] $items
     */
    public function __construct(
        string $key,
        array $items
    ) {
        $this->key = $key;
        $this->items = $items;
        $this->setNow();
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'items' => array_values(
                array_map(function (Item $item) {
                    return $item->toArray();
                }, $this->items)
            ),
        ];
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
            array_map(function (array $item) {
                return Item::createFromArray($item);
            }, json_decode($payload, true)['items']),
        ];
    }
}
