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

/**
 * Class BaseTest.
 */
abstract class BaseTest
{
    /**
     * @var Repository
     *
     * Repository
     */
    protected static $repository;

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
     */
    public static function resetScenario()
    {
        self::get('search_bundle.elastica_wrapper')->deleteIndex(self::$key);
        self::get('search_bundle.elastica_wrapper')->createIndexMapping(self::$key, 1, 1, null);
        self::get('search_bundle.elastica_wrapper')->createIndexMapping(self::$anotherKey, 1, 1, null);

        self::$repository = self::get(static::getRepositoryServiceName());
        self::$repository->setKey(self::$key);
        $items = Yaml::parse(file_get_contents(__DIR__ . '/../../items.yml'));
        foreach ($items['items'] as $item) {
            self::$repository->addItem(
                Item::createFromArray($item)
            );
        }

        self::$repository->flush(500);
    }
}
