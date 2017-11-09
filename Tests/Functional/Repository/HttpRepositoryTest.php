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
     * @param string     $appId
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setCredentials($appId ?? self::$appId, 'xxx');

        return $repository->query($query);
    }

    /**
     * Delete using the bus.
     *
     * @param ItemUUID[] $itemsUUID
     * @param string     $appId
     */
    public function deleteItems(
        array $itemsUUID,
        string $appId = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setCredentials($appId ?? self::$appId, 'xxx');
        foreach ($itemsUUID as $itemUUID) {
            $repository->deleteItem($itemUUID);
        }
        $repository->flush();
    }

    /**
     * Add items using the bus.
     *
     * @param Item[] $items
     * @param string $appId
     */
    public function addItems(
        array $items,
        string $appId = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setCredentials($appId ?? self::$appId, 'xxx');
        foreach ($items as $item) {
            $repository->addItem($item);
        }
        $repository->flush();
    }

    /**
     * Reset repository using the bus.
     *
     * @param string $language
     * @param string $appId
     */
    public function reset(
        string $language = null,
        string $appId = null
    ) {
        $repository = $this->get('puntmig_search.repository_search');
        $repository->setCredentials($appId ?? self::$appId, 'xxx');
        $repository->reset($language);
    }
}
