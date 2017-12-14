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

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query as QueryModel;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Server\ApisearchServerBundle;
use Apisearch\Server\Domain\Command\Delete as DeleteCommand;
use Apisearch\Server\Domain\Command\Index as IndexCommand;
use Apisearch\Server\Domain\Command\Reset as ResetCommand;
use Apisearch\Server\Domain\Query\Query;
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
                    'repositories' => [
                        'search' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'token' => 'xxx',
                            'test' => true,
                            'indexes' => [
                                self::$index,
                            ],
                            'search' => [
                                'repository_service' => 'apisearch.server.elastica_repository',
                                'in_memory' => false,
                            ],
                            'event' => [
                                'repository_service' => 'apisearch.event_repository_search.'.self::$index,
                                'in_memory' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'services' => [
                        'apisearch.server.middleware.domain_events' => [
                            'alias' => 'apisearch.server.middleware.inline_domain_events',
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
    public static $anotherIndex = 'default_0';

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
        self::reset($language, self::$appId);
        self::reset($language, self::$anotherAppId);

        $items = Yaml::parse(file_get_contents(__DIR__.'/../items.yml'));
        $itemsInstances = [];
        foreach ($items['items'] as $item) {
            if (isset($item['indexed_metadata']['created_at'])) {
                $date = new \DateTime($item['indexed_metadata']['created_at']);
                $item['indexed_metadata']['created_at'] = $date->format(DATE_ATOM);
            }
            $itemsInstances[] = Item::createFromArray($item);
        }
        self::addItems($itemsInstances, self::$appId);
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
            ->handle(new DeleteCommand(
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
    public function addItems(
        array $items,
        string $appId = null,
        string $index = null
    ) {
        self::$container
            ->get('tactician.commandbus')
            ->handle(new IndexCommand(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $items
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ResetCommand(
                RepositoryReference::create(
                    $appId ?? self::$appId,
                    $index ?? self::$index
                ),
                $language
            ));
    }
}
