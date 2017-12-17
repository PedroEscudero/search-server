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

namespace Apisearch\Server\Tests\Functional;

use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Server\ApisearchServerBundle;
use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Apisearch\Server\Domain\Command\CreateIndex;
use Apisearch\Server\Domain\Command\DeleteEventsIndex;
use Apisearch\Server\Domain\Command\DeleteIndex;
use Apisearch\Server\Domain\Command\DeleteItems;
use Apisearch\Server\Domain\Command\IndexItems;
use Apisearch\Server\Domain\Command\ResetIndex;
use Apisearch\Server\Domain\Query\ListEvents;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Server\Domain\Query\StatsEvents;
use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ApisearchServerBundleFunctionalTest.
 */
abstract class ApisearchServerBundleFunctionalTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        $imports = [
            ['resource' => '@ApisearchServerBundle/Resources/config/tactician.yml'],
        ];

        if (!static::logDomainEvents()) {
            $imports[] = ['resource' => '@ApisearchServerBundle/Resources/test/middlewares.yml'];
        }

        return new BaseKernel(
            [
                BaseBundle::class,
                ApisearchServerBundle::class,
            ], [
                'imports' => $imports,
                'parameters' => [
                    'kernel.secret' => 'sdhjshjkds',
                ],
                'framework' => [
                    'test' => true,
                ],
                'apisearch' => [
                    'middleware_domain_events_service' => 'apisearch.server.middleware.inline_events',
                    'repositories' => [
                        'search' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'token' => 'xxx',
                            'test' => true,
                            'indexes' => [
                                self::$index,
                                self::$anotherIndex,
                            ],
                            'search' => [
                                'repository_service' => 'apisearch.server.elastica_repository',
                                'in_memory' => false,
                            ],
                            'event' => [
                                'repository_service' => 'apisearch.server.elastica_event_repository',
                                'in_memory' => false,
                            ],
                        ],
                        'search_http' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'token' => 'xxx',
                            'test' => true,
                            'indexes' => [
                                self::$index,
                                self::$anotherIndex,
                            ],
                            'search' => [
                                'in_memory' => false,
                            ],
                            'event' => [
                                'in_memory' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '@ApisearchServerBundle/Resources/config/routing.yml',
            ],
            'test', true
        );
    }

    /**
     * Log domain events.
     *
     * @return bool
     */
    protected static function logDomainEvents(): bool
    {
        return true;
    }

    /**
     * @var string
     *
     * App id
     */
    public static $appId = 'test';

    /**
     * @var string
     *
     * App id
     */
    public static $index = 'default';

    /**
     * @var string
     *
     * App id
     */
    public static $anotherAppId = 'another_test';

    /**
     * @var string
     *
     * App id
     */
    public static $anotherInexistentAppId = 'another_test_not_exists';

    /**
     * @var string
     *
     * App id
     */
    public static $anotherIndex = 'another_index';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::resetScenario();
    }

    /**
     * Reset scenario.
     *
     * @param null|string $language
     */
    public static function resetScenario(? string $language = null)
    {
        try {
            self::deleteIndex(self::$appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            self::deleteEventsIndex(self::$appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            self::deleteIndex(self::$anotherAppId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            self::deleteEventsIndex(self::$anotherAppId);
        } catch (ResourceNotAvailableException $e) {
        }

        self::createIndex($language, self::$appId);
        self::createEventsIndex(self::$appId);
        self::createIndex($language, self::$anotherAppId);
        self::createEventsIndex(self::$anotherAppId);

        $items = Yaml::parse(file_get_contents(__DIR__.'/../items.yml'));
        $itemsInstances = [];
        foreach ($items['items'] as $item) {
            if (isset($item['indexed_metadata']['created_at'])) {
                $date = new \DateTime($item['indexed_metadata']['created_at']);
                $item['indexed_metadata']['created_at'] = $date->format(DATE_ATOM);
            }
            $itemsInstances[] = Item::createFromArray($item);
        }
        self::indexItems($itemsInstances, self::$appId);
    }

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
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new Query(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $query
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteItems(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $itemsUUID
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new IndexItems(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $items
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ResetIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new CreateIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $language
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new CreateEventsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                3,
                2
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteEventsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
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
     */
    public function listEvents(
        ?string $name = null,
        ?int $from = null,
        ?int $to = null,
        ?int $length = null,
        ?int $offset = null,
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ListEvents(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $name,
                $from,
                $to,
                $length,
                $offset
            ));
    }

    /**
     * List all events stats using the bus.
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new StatsEvents(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $from,
                $to
            ));
    }
}
