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

namespace Puntmig\Search\Server\Tests\Functional;

use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query as QueryModel;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Domain\Command\Delete as DeleteCommand;
use Puntmig\Search\Server\Domain\Command\Index as IndexCommand;
use Puntmig\Search\Server\Domain\Command\Reset as ResetCommand;
use Puntmig\Search\Server\Domain\Query\Query;
use Puntmig\Search\Server\PuntmigSearchServerBundle;

/**
 * Class SearchBundleFunctionalTest.
 */
abstract class PuntmigSearchServerBundleFunctionalTest extends BaseFunctionalTest
{
    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel(): KernelInterface
    {
        $imports = [
            ['resource' => '@PuntmigSearchServerBundle/Resources/config/tactician.yml'],
        ];

        if (!static::logDomainEvents()) {
            $imports[] = ['resource' => '@PuntmigSearchServerBundle/Resources/test/logDomainEventsMiddleware.yml'];
        }

        return new BaseKernel(
            [
                BaseBundle::class,
                PuntmigSearchServerBundle::class,
            ], [
                'imports' => $imports,
                'parameters' => [
                    'token_server_endpoint' => '//',
                    'kernel.secret' => 'sdhjshjkds',
                ],
                'framework' => [
                    'test' => true,
                ],
                'puntmig_search' => [
                    'repositories' => [
                        'search' => [
                            'endpoint' => 'xxx',
                            'app_id' => self::$appId,
                            'secret' => 'xxx',
                            'test' => true,
                            'search' => [
                                'repository_service' => 'search_server.elastica_repository',
                                'in_memory' => false,
                            ],
                            'event' => [
                                'repository_service' => 'puntmig_search.event_repository_search',
                                'in_memory' => true,
                            ],
                        ],
                    ],
                ],
                [
                    'services' => [
                    ],
                ],
            ],
            [
                '@PuntmigSearchServerBundle/Resources/config/routing.yml',
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
    public static $anotherAppId = 'another_test';

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
     *
     * @return Result
     */
    public function query(
        QueryModel $query,
        string $appId = null
    ) {
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new Query(
                $appId ?? self::$appId,
                $query
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteCommand(
                $appId ?? self::$appId,
                $itemsUUID
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new IndexCommand(
                $appId ?? self::$appId,
                $items
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ResetCommand(
                $appId ?? self::$appId,
                $language
            ));
    }
}
