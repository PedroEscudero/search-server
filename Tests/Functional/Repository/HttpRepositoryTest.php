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

namespace Puntmig\Search\Server\Tests\Functional\Repository;

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query as QueryModel;
use Puntmig\Search\Result\Result;

/**
 * Class HttpRepositoryTest.
 */
class HttpRepositoryTest extends RepositoryTest
{
    /**
     * Query using the bus.
     *
     * @param QueryModel $query
     * @param string     $key
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $key = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setKey($key ?? self::$key);

        return $repository->query($query);
    }

    /**
     * Delete using the bus.
     *
     * @param ItemUUID[] $itemsUUID
     * @param string     $key
     */
    public function deleteItems(
        array $itemsUUID,
        string $key = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setKey($key ?? self::$key);
        foreach ($itemsUUID as $itemUUID) {
            $repository->deleteItem($itemUUID);
        }
        $repository->flush();
    }

    /**
     * Add items using the bus.
     *
     * @param Item[] $items
     * @param string $key
     */
    public function addItems(
        array $items,
        string $key = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setKey($key ?? self::$key);
        foreach ($items as $item) {
            $repository->addItem($item);
        }
        $repository->flush();
    }

    /**
     * Reset repository using the bus.
     *
     * @param string $language
     * @param string $key
     */
    public function reset(
        string $language = null,
        string $key = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setKey($key ?? self::$key);
        $repository->reset($language);
    }
}
