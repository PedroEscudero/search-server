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
use Puntmig\Search\Query\Query;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Domain\Command\DeleteCommand;
use Puntmig\Search\Server\Domain\Command\IndexCommand;
use Puntmig\Search\Server\Domain\Command\QueryCommand;
use Puntmig\Search\Server\Domain\Command\ResetCommand;
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
    protected static function getKernel() : KernelInterface
    {
        $imports = [
            ['resource' => '@BaseBundle/Resources/config/providers.yml'],
            ['resource' => '@BaseBundle/Resources/test/doctrine.test.yml'],
            ['resource' => '@PuntmigSearchServerBundle/Resources/config/tactician.yml'],
        ];

        if (static::useInMemoryEventStore()) {
            $imports[] = ['resource' => '@PuntmigSearchServerBundle/Resources/test/eventStore.yml'];
        }

        return new BaseKernel(
            [
                BaseBundle::class,
                PuntmigSearchServerBundle::class,
            ], [
                'imports' => $imports,
                'framework' => [
                    'test' => true,
                ],
                'puntmig_search' => [
                    'repositories' => [
                        'search' => [
                            'endpoint' => 'xxx',
                            'secret' => 'hjk45hj4k4',
                            'repository_service' => static::getRepositoryServiceName(),
                            'test' => true,
                        ],
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
     * get repository service name.
     *
     * @return string
     */
    abstract protected static function getRepositoryServiceName() : string;

    /**
     * Use in memory event store.
     *
     * @return bool
     */
    protected static function useInMemoryEventStore() : bool
    {
        return true;
    }

    /**
     * @var string
     *
     * Used api key
     */
    protected static $key = 'hjk45hj4k4';

    /**
     * @var string
     *
     * Another used api key
     */
    protected static $anotherKey = '5h43jk5h43';

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
        self::reset($language, self::$key);
        $items = Yaml::parse(file_get_contents(__DIR__ . '/../items.yml'));
        $itemsInstances = [];
        foreach ($items['items'] as $item) {
            if (isset($item['indexed_metadata']['created_at'])) {
                $date = new \DateTime($item['indexed_metadata']['created_at']);
                $item['indexed_metadata']['created_at'] = $date->format(DATE_ATOM);
            }
            $itemsInstances[] = Item::createFromArray($item);
        }
        self::addItems($itemsInstances, self::$key);
    }

    /**
     * Query using the bus.
     *
     * @param Query  $query
     * @param string $key
     *
     * @return Result
     */
    public function query(
        Query $query,
        string $key = null
    ) {
        return self::$container
            ->get('tactician.commandbus')
            ->handle(new QueryCommand(
                $key ?? self::$key,
                $query
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new DeleteCommand(
                $key ?? self::$key,
                $itemsUUID
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new IndexCommand(
                $key ?? self::$key,
                $items
            ));
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
        self::$container
            ->get('tactician.commandbus')
            ->handle(new ResetCommand(
                $key ?? self::$key,
                $language
            ));
    }
}
