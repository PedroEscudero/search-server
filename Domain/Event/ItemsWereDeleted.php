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

use Puntmig\Search\Model\ItemUUID;

/**
 * Class ItemsWereDeleted.
 */
class ItemsWereDeleted extends DomainEvent
{
    /**
     * @var ItemUUID[]
     *
     * Items UUID
     */
    private $itemsUUID;

    /**
     * ItemsWasIndexed constructor.
     *
     * @param string     $appId
     * @param string     $key
     * @param ItemUUID[] $itemsUUID
     */
    public function __construct(
        string $appId,
        string $key,
        array $itemsUUID
    ) {
        $this->appId = $appId;
        $this->key = $key;
        $this->itemsUUID = $itemsUUID;
        $this->setNow();
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'items' => array_values(
                array_map(function (ItemUUID $itemUUID) {
                    return $itemUUID->toArray();
                }, $this->itemsUUID)
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
    public static function fromPayload(string $payload): array
    {
        return [
            array_map(function (array $itemUUID) {
                return ItemUUID::createFromArray($itemUUID);
            }, json_decode($payload, true)['items']),
        ];
    }
}
