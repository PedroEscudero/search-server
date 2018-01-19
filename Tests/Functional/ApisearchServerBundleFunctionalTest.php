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

use Apisearch\Config\Config;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Server\ApisearchServerBundle;
use Apisearch\Server\Domain\Command\ConfigureIndex;
use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Apisearch\Server\Domain\Command\CreateIndex;
use Apisearch\Server\Domain\Command\CreateLogsIndex;
use Apisearch\Server\Domain\Command\DeleteEventsIndex;
use Apisearch\Server\Domain\Command\DeleteIndex;
use Apisearch\Server\Domain\Command\DeleteItems;
use Apisearch\Server\Domain\Command\DeleteLogsIndex;
use Apisearch\Server\Domain\Command\IndexItems;
use Apisearch\Server\Domain\Command\ResetIndex;
use Apisearch\Server\Domain\Query\CheckIndex;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Server\Domain\Query\QueryEvents;
use Apisearch\Server\Domain\Query\QueryLogs;
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
                'apisearch_server' => [
                    'middleware_domain_events_service' => 'apisearch_server.middleware.inline_events',
                    'middleware_logs_service' => 'apisearch_server.middleware.inline_logs',
                    'config' => [
                        'repository' => [
                            'config_path' => '/tmp/config_{app_id}_{index_id}',
                            'shards' => 1,
                            'replicas' => 0,
                        ],
                        'event_repository' => [
                            'shards' => 1,
                            'replicas' => 0,
                        ],
                        'log_repository' => [
                            'shards' => 1,
                            'replicas' => 0,
                        ],
                    ],
                ],
                'apisearch' => [
                    'repositories' => [
                        'main' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'token' => 'xxx',
                            'test' => true,
                            'indexes' => [
                                self::$index => self::$index,
                                self::$anotherIndex => self::$anotherIndex,
                            ],
                            'search' => [
                                'repository_service' => 'apisearch_server.elastica_repository',
                                'in_memory' => false,
                            ],
                            'event' => [
                                'repository_service' => 'apisearch_server.elastica_event_repository',
                                'in_memory' => false,
                            ],
                            'log' => [
                                'repository_service' => 'apisearch_server.elastica_log_repository',
                                'in_memory' => false,
                            ],
                        ],
                        'search_http' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'token' => 'xxx',
                            'test' => true,
                            'indexes' => [
                                self::$index => self::$index,
                                self::$anotherIndex => self::$anotherIndex,
                            ],
                            'search' => [
                                'in_memory' => false,
                            ],
                            'event' => [
                                'in_memory' => false,
                            ],
                            'log' => [
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
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function tearDownAfterClass()
    {
        self::deleteEverything();
    }

    /**
     * Reset scenario.
     */
    public static function resetScenario()
    {
        self::deleteEverything();
        self::createIndex(self::$appId);
        self::createEventsIndex(self::$appId);
        self::createLogsIndex(self::$appId);
        self::createIndex(self::$anotherAppId);
        self::createEventsIndex(self::$anotherAppId);
        self::createLogsIndex(self::$anotherAppId);

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
     * Clean all tests data.
     */
    public static function deleteEverything()
    {
        self::deleteAppIdIndexes(self::$appId);
        self::deleteAppIdIndexes(self::$anotherAppId);
    }

    /**
     * Delete index and catch.
     *
     * @param string $appId
     */
    private static function deleteAppIdIndexes(string $appId)
    {
        try {
            self::deleteIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            self::deleteEventsIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            self::deleteLogsIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
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
     * @param string $appId
     * @param string $index
     */
    public function createIndex(
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new CreateIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
    }

    /**
     * Configure index using the bus.
     *
     * @param Config $config
     * @param string $appId
     * @param string $index
     */
    public function configureIndex(
        Config $config,
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ConfigureIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $config
            ));
    }

    /**
     * Check index.
     *
     * @param string $appId
     * @param string $index
     *
     * @return bool
     */
    public function checkIndex(
        string $appId = null,
        string $index = null
    ): bool {
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new CheckIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
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
                )
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
     * Query events.
     *
     * @param QueryModel $query
     * @param int|null   $from
     * @param int|null   $to
     * @param string     $appId
     * @param string     $index
     */
    public function queryEvents(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null
    ) {
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new QueryEvents(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $query,
                $from,
                $to
            ));
    }

    /**
     * Create log index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function createLogsIndex(
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new CreateLogsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
    }

    /**
     * Delete log index using the bus.
     *
     * @param string $appId
     * @param string $index
     */
    public function deleteLogsIndex(
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteLogsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                )
            ));
    }

    /**
     * Query logs.
     *
     * @param QueryModel $query
     * @param int|null   $from
     * @param int|null   $to
     * @param string     $appId
     * @param string     $index
     */
    public function queryLogs(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null
    ) {
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new QueryLogs(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $query,
                $from,
                $to
            ));
    }
}
