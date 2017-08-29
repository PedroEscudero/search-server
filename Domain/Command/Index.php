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

use Puntmig\Search\Model\Item;
use Puntmig\Search\Server\Domain\WithKey;

/**
 * Class Index.
 */
class Index extends WithKey
{
    /**
     * @var Item[]
     *
     * Items
     */
    private $items;

    /**
     * IndexCommand constructor.
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
    }

    /**
     * Get Items.
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
