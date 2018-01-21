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

use Apisearch\App\AppRepository;
use Apisearch\Config\Config;
use Apisearch\Event\EventRepository;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\Repository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class HttpRepositoryTest.
 */
class HttpRepositoryTest extends RepositoryTest
{
    use TokenTest;

    /**
     * Query using the bus.
     *
     * @param QueryModel $query
     * @param string     $appId
     * @param string     $index
     * @param string     $token
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        return $this
            ->configureRepository($appId, $index, $token)
            ->query($query);
    }

    /**
     * Delete using the bus.
     *
     * @param ItemUUID[] $itemsUUID
     * @param string     $appId
     * @param string     $index
     * @param string     $token
     */
    public function deleteItems(
        array $itemsUUID,
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $repository = $this->configureRepository($appId, $index, $token);
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
     * @param string $token
     */
    public function indexItems(
        array $items,
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $repository = $this->configureRepository($appId, $index, $token);
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
     * @param string $token
     */
    public function resetIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureRepository($appId, $index, $token)
            ->resetIndex();
    }

    /**
     * Create index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     */
    public function createIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureRepository($appId, $index, $token)
            ->createIndex();
    }

    /**
     * Configure index using the bus.
     *
     * @param Config $config
     * @param string $appId
     * @param string $index
     * @param string $token
     */
    public function configureIndex(
        Config $config,
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureRepository($appId, $index, $token)
            ->configureIndex($config);
    }

    /**
     * Check index.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     *
     * @return bool
     */
    public function checkIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ): bool {
        return $this
            ->configureRepository($appId, $index, $token)
            ->checkIndex();
    }

    /**
     * Delete index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     */
    public function deleteIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureRepository($appId, $index, $token)
            ->deleteIndex();
    }

    /**
     * Add token.
     *
     * @param Token  $newToken
     * @param string $appId
     * @param string $token
     */
    public function addToken(
        Token $newToken,
        string $appId = null,
        string $token = null
    ) {
        $this
            ->configureAppRepository($appId, $token)
            ->addToken($newToken);
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     * @param string    $appId
     * @param string    $token
     */
    public function deleteToken(
        TokenUUID $tokenUUID,
        string $appId = null,
        string $token = null
    ) {
        $this
            ->configureAppRepository($appId, $token)
            ->deleteToken($tokenUUID);
    }

    /**
     * Create event index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     */
    public function createEventsIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureEventsRepository($appId, $index, $token)
            ->createIndex(3, 2);
    }

    /**
     * Delete event index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     */
    public function deleteEventsIndex(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureEventsRepository($appId, $index, $token)
            ->deleteIndex();
    }

    /**
     * List all events using the bus.
     *
     * @param string|null $name
     * @param int|null    $from
     * @param int|null    $to
     * @param int|null    $length
     * @param int|null    $offset
     * @param string      $appId
     * @param string      $index
     * @param string      $token
     */
    public function listEvents(
        string $name = null,
        int $from = null,
        int $to = null,
        int $length = null,
        int $offset = null,
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $this
            ->configureEventsRepository($appId, $index, $token)
            ->all(
                $name,
                $from,
                $to,
                $length,
                $offset
            );
    }

    /**
     * Configure repository.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     *
     * @return Repository
     */
    public function configureRepository(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $index = $index ?? self::$index;
        $repository = $this->get('apisearch.repository_search_http.'.$index);
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                $index
            ),
            ($token ?? 'xxx')
        );

        return $repository;
    }

    /**
     * Configure app repository.
     *
     * @param string $appId
     * @param string $token
     *
     * @return AppRepository
     */
    private function configureAppRepository(
        string $appId = null,
        string $token = null
    ) {
        $repository = $this->get('apisearch.app_repository_search_http');
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                ''
            ),
            ($token ?? 'xxx')
        );

        return $repository;
    }

    /**
     * Configure events repository.
     *
     * @param string $appId
     * @param string $index
     * @param string $token
     *
     * @return EventRepository
     */
    private function configureEventsRepository(
        string $appId = null,
        string $index = null,
        string $token = null
    ) {
        $index = $index ?? self::$index;
        $repository = $this->get('apisearch.event_repository_search_http.'.$index);
        $repository->setCredentials(
            RepositoryReference::create(
                $appId ?? self::$appId,
                $index
            ),
            ($token ?? 'xxx')
        );

        return $repository;
    }
}
