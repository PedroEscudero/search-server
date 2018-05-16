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
use Apisearch\Model\User;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Server\ApisearchServerBundle;
use Apisearch\Server\Domain\Command\AddInteraction;
use Apisearch\Server\Domain\Command\AddToken;
use Apisearch\Server\Domain\Command\ConfigureIndex;
use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Apisearch\Server\Domain\Command\CreateIndex;
use Apisearch\Server\Domain\Command\CreateLogsIndex;
use Apisearch\Server\Domain\Command\DeleteAllInteractions;
use Apisearch\Server\Domain\Command\DeleteEventsIndex;
use Apisearch\Server\Domain\Command\DeleteIndex;
use Apisearch\Server\Domain\Command\DeleteItems;
use Apisearch\Server\Domain\Command\DeleteLogsIndex;
use Apisearch\Server\Domain\Command\DeleteToken;
use Apisearch\Server\Domain\Command\IndexItems;
use Apisearch\Server\Domain\Command\ResetIndex;
use Apisearch\Server\Domain\Query\CheckHealth;
use Apisearch\Server\Domain\Query\CheckIndex;
use Apisearch\Server\Domain\Query\Ping;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Server\Domain\Query\QueryEvents;
use Apisearch\Server\Domain\Query\QueryLogs;
use Apisearch\Server\Exception\ErrorException;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use Apisearch\User\Interaction;
use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

set_error_handler(function ($code, $message, $file, $line, $context) {
    throw new ErrorException($message, $code);
});

/**
 * Class ApisearchServerBundleFunctionalTest.
 */
abstract class ApisearchServerBundleFunctionalTest extends BaseFunctionalTest
{
    /**
     * Get container service.
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public static function getStatic(string $serviceName)
    {
        return self::$container->get($serviceName);
    }

    /**
     * Container has service.
     *
     * @param string $serviceName
     *
     * @return bool
     */
    public static function hasStatic(string $serviceName): bool
    {
        return self::$container->has($serviceName);
    }

    /**
     * Get container parameter.
     *
     * @param string $parameterName
     *
     * @return mixed
     */
    public static function getParameterStatic(string $parameterName)
    {
        return self::$container->getParameter($parameterName);
    }

    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        $imports = [
            ['resource' => '@ApisearchServerBundle/Resources/config/tactician.yml'],
            [
                'resource' => '@ApisearchServerBundle/app_deploy.yml',
                'ignore_errors' => true
            ]
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
                    'god_token' => self::$godToken,
                    'ping_token' => self::$pingToken,
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
                            'endpoint' => '~',
                            'app_id' => self::$appId,
                            'token' => '~',
                            'test' => true,
                            'indexes' => [
                                self::$index => self::$index,
                                self::$anotherIndex => self::$anotherIndex,
                            ],
                            'search' => [
                                'repository_service' => 'apisearch_server.items_repository',
                                'in_memory' => false,
                            ],
                            'app' => [
                                'repository_service' => 'apisearch_server.app_repository',
                                'in_memory' => false,
                            ],
                            'user' => [
                                'repository_service' => 'apisearch_server.user_repository',
                                'in_memory' => false,
                            ],
                            'event' => [
                                'repository_service' => 'apisearch_server.events_repository',
                                'in_memory' => false,
                            ],
                            'log' => [
                                'repository_service' => 'apisearch_server.logs_repository',
                                'in_memory' => false,
                            ],
                        ],
                        'search_http' => [
                            'endpoint' => '~',
                            'app_id' => self::$appId,
                            'token' => '~',
                            'test' => true,
                            'indexes' => [
                                self::$index => self::$index,
                                self::$anotherIndex => self::$anotherIndex,
                            ],
                            'search' => [
                                'in_memory' => false,
                            ],
                            'app' => [
                                'in_memory' => false,
                            ],
                            'event' => [
                                'in_memory' => false,
                            ],
                            'log' => [
                                'in_memory' => false,
                            ],
                        ],
                        'search_socket' => [
                            'endpoint' => 'http://127.0.0.1:8999',
                            'app_id' => self::$appId,
                            'token' => self::$godToken,
                            'indexes' => [
                                self::$index => self::$index,
                                self::$anotherIndex => self::$anotherIndex,
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
     * God token
     */
    public static $godToken = '0e4d75ba-c640-44c1-a745-06ee51db4e93';

    /**
     * @var string
     *
     * Ping token
     */
    public static $pingToken = '6326d504-0a5f-f1ae-7344-8e70b75fcde9';

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

        static::createLogsIndex(self::$appId);
        static::createLogsIndex(self::$appId, '');
        static::createEventsIndex(self::$appId);
        static::createEventsIndex(self::$appId, '');
        static::createIndex(self::$appId);

        static::createLogsIndex(self::$anotherAppId);
        static::createLogsIndex(self::$anotherAppId, '');
        static::createEventsIndex(self::$anotherAppId);
        static::createEventsIndex(self::$anotherAppId, '');
        static::createIndex(self::$anotherAppId);

        $items = Yaml::parse(file_get_contents(__DIR__.'/../items.yml'));
        $itemsInstances = [];
        foreach ($items['items'] as $item) {
            if (isset($item['indexed_metadata']['created_at'])) {
                $date = new \DateTime($item['indexed_metadata']['created_at']);
                $item['indexed_metadata']['created_at'] = $date->format(DATE_ATOM);
            }
            $itemsInstances[] = Item::createFromArray($item);
        }
        static::indexItems($itemsInstances, self::$appId);
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
            static::deleteIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            static::deleteEventsIndex($appId, '');
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            static::deleteEventsIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            static::deleteLogsIndex($appId, '');
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            static::deleteLogsIndex($appId);
        } catch (ResourceNotAvailableException $e) {
        }
        try {
            static::deleteAllInteractions($appId);
        } catch (ResourceNotAvailableException $e) {
        } catch (ErrorException $e) {
        }
    }

    /**
     * Query using the bus.
     *
     * @param QueryModel $query
     * @param string     $appId
     * @param string     $index
     * @param Token      $token
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null,
        string $index = null,
        Token $token = null
    ): Result {
        return self::getStatic('tactician.commandbus')
            ->handle(new Query(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
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
     * @param Token      $token
     */
    public function deleteItems(
        array $itemsUUID,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        return self::getStatic('tactician.commandbus')
            ->handle(new DeleteItems(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
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
     * @param Token  $token
     */
    public static function indexItems(
        array $items,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new IndexItems(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    ),
                $items
            ));
    }

    /**
     * Reset index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public function resetIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new ResetIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Create index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function createIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new CreateIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Configure index using the bus.
     *
     * @param Config $config
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public function configureIndex(
        Config $config,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new ConfigureIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    ),
                $config
            ));
    }

    /**
     * Check index.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     *
     * @return bool
     */
    public function checkIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ): bool {
        return self::getStatic('tactician.commandbus')
            ->handle(new CheckIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Delete index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function deleteIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new DeleteIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Add token.
     *
     * @param Token  $newToken
     * @param string $appId
     * @param Token  $token
     */
    public static function addToken(
        Token $newToken,
        string $appId = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new AddToken(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    ''
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    ),
                $newToken
            ));
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     * @param string    $appId
     * @param Token     $token
     */
    public static function deleteToken(
        TokenUUID $tokenUUID,
        string $appId = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new DeleteToken(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    ''
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    ),
                $tokenUUID
            ));
    }

    /**
     * Create event index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function createEventsIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new CreateEventsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Delete event index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function deleteEventsIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new DeleteEventsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
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
     * @param Token      $token
     */
    public function queryEvents(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        return self::getStatic('tactician.commandbus')
            ->handle(new QueryEvents(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
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
     * @param Token  $token
     */
    public static function createLogsIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new CreateLogsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Delete log index using the bus.
     *
     * @param string $appId
     * @param string $index
     * @param Token  $token
     */
    public static function deleteLogsIndex(
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new DeleteLogsIndex(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
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
     * @param Token      $token
     */
    public function queryLogs(
        QueryModel $query,
        ?int $from = null,
        ?int $to = null,
        string $appId = null,
        string $index = null,
        Token $token = null
    ) {
        return self::getStatic('tactician.commandbus')
            ->handle(new QueryLogs(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    ),
                $query,
                $from,
                $to
            ));
    }

    /**
     * Add interaction.
     *
     * @param string $userId
     * @param string $itemUUIDComposed
     * @param int    $weight
     * @param string $appId
     * @param Token  $token
     */
    public function addInteraction(
        string $userId,
        string $itemUUIDComposed,
        int $weight,
        string $appId,
        Token $token
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new AddInteraction(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    ''
                ),
                $token,
                new Interaction(
                    new User($userId),
                    ItemUUID::createByComposedUUID($itemUUIDComposed),
                    $weight
                )
            ));
    }

    /**
     * Delete all interactions.
     *
     * @param string $appId
     * @param Token  $token
     */
    public static function deleteAllInteractions(
        string $appId,
        Token $token = null
    ) {
        self::getStatic('tactician.commandbus')
            ->handle(new DeleteAllInteractions(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    ''
                ),
                $token ??
                    new Token(
                        TokenUUID::createById(self::getParameterStatic('apisearch_server.god_token')),
                        $appId ?? self::$appId
                    )
            ));
    }

    /**
     * Ping.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function ping(Token $token = null): bool
    {
        return self::getStatic('tactician.commandbus')->handle(new Ping());
    }

    /**
     * Check health.
     *
     * @param Token $token
     *
     * @return array
     */
    public function checkHealth(Token $token = null): array
    {
        return self::getStatic('tactician.commandbus')->handle(new CheckHealth());
    }
}
