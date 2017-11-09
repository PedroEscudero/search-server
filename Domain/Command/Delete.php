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

namespace Puntmig\Search\Server\Domain\Command;

use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Server\Domain\WithAppId;

/**
 * Class Delete.
 */
class Delete extends WithAppId
{
    /**
     * @var ItemUUID[]
     *
     * Items UUID
     */
    private $itemsUUID;

    /**
     * DeleteCommand constructor.
     *
     * @param string     $appId
     * @param ItemUUID[] $itemsUUID
     */
    public function __construct(
        string $appId,
        array $itemsUUID
    ) {
        $this->appId = $appId;
        $this->itemsUUID = $itemsUUID;
    }

    /**
     * Get Items.
     *
     * @return ItemUUID[]
     */
    public function getItemsUUID(): array
    {
        return $this->itemsUUID;
    }
}
