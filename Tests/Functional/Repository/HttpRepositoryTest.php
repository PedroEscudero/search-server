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

namespace Apisearch\Server\Tests\Functional\Repository;

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\Repository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;

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
     * @param string     $index
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null,
        string $index = null
    ) {
        return $this
            ->configureRepository($appId, $index)
            ->query($query);
    }

    /**
     * Delete using the bus.
     *
     * @param ItemUUID[] $itemsUUID
     * @param string     $appId
     * @param string     $index
     */
    public function deleteItems(
        array $itemsUUID,
        string $appId = null,
        string $index = null
    ) {
        $repository = $this->configureRepository($appId, $index);
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
     * @param string $index
     */
    public function addItems(
        array $items,
        string $appId = null,
        string $index = null
    ) {
        $repository = $this->configureRepository($appId, $index);
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
     * @param string $index
     */
    public function reset(
        string $language = null,
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureRepository($appId, $index)
            ->reset($language);
    }

    /**
     * Configure repository.
     *
     * @param string $appId
     * @param string $index
     *
     * @return Repository
     */
    private function configureRepository(
        string $appId = null,
        string $index = null
    ) {
        $index = $index ?? self::$index;
        $repository = $this->get('apisearch.repository_search.'.$index);
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                $index
            ),
            'xxx'
        );

        return $repository;
    }
}
