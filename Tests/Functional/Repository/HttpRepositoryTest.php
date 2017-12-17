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

use Apisearch\Event\EventRepository;
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
    public function indexItems(
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
     * Reset index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function resetIndex(
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureRepository($appId, $index)
            ->resetIndex();
    }

    /**
     * Create index using the bus.
     *
     * @param string $language
     * @param string $appId
     * @param string $index
     */
    public function createIndex(
        string $language = null,
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureRepository($appId, $index)
            ->createIndex($language);
    }

    /**
     * Delete index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function deleteIndex(
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureRepository($appId, $index)
            ->deleteIndex();
    }

    /**
     * Create event index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function createEventsIndex(
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureEventsRepository($appId, $index)
            ->createIndex(3, 2);
    }

    /**
     * Delete event index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function deleteEventsIndex(
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureEventsRepository($appId, $index)
            ->deleteIndex();
    }

    /**
     * List all events using the bus
     *
     * @param string|null $name
     * @param int|null    $from
     * @param int|null    $to
     * @param int|null    $length
     * @param int|null    $offset
     * @param string      $appId
     * @param string      $index
     */
    public function listEvents(
        string $name = null,
        int $from = null,
        int $to = null,
        int $length = null,
        int $offset = null,
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureEventsRepository($appId, $index)
            ->all(
                $name,
                $from,
                $to,
                $length,
                $offset
            );
    }

    /**
     * List all events stats using the bus
     *
     * @param int|null $from
     * @param int|null $to
     * @param string   $appId
     * @param string   $index
     */
    public function statsEvents(
        int $from = null,
        int $to = null,
        string $appId = null,
        string $index = null
    ) {
        $this
            ->configureEventsRepository($appId, $index)
            ->stats(
                $from,
                $to
            );
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
        $repository = $this->get('apisearch.repository_search_http.'.$index);
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                $index
            ),
            'xxx'
        );

        return $repository;
    }

    /**
     * Configure events repository.
     *
     * @param string $appId
     * @param string $index
     *
     * @return EventRepository
     */
    private function configureEventsRepository(
        string $appId = null,
        string $index = null
    ) {
        $index = $index ?? self::$index;
        $repository = $this->get('apisearch.event_repository_search_http.'.$index);
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
