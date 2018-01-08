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

use Apisearch\Model\ItemUUID;

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
     * @param ItemUUID[] $itemsUUID
     */
    public function __construct(array $itemsUUID)
    {
        $this->itemsUUID = $itemsUUID;
        $this->setNow();
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function readableOnlyToArray(): array
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
        return [
            array_map(function (array $itemUUID) {
                return ItemUUID::createFromArray($itemUUID);
            }, json_decode($data, true)['items']),
        ];
    }
}
