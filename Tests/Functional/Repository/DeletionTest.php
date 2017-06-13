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

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Server\Elastica\ElasticaWrapper;

/**
 * Class DeletionTest.
 */
trait DeletionTest
{
    /**
     * Test item deletions.
     */
    public function testItemDeletions()
    {
        static::$repository->deleteItem(new ItemUUID('1', 'product'));
        self::$repository->flush();
        $this->assertSame(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, ElasticaWrapper::ITEM_TYPE)->count());
        static::$repository->deleteItem(new ItemUUID('1', 'product'));
        self::$repository->flush();
        $this->assertSame(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, ElasticaWrapper::ITEM_TYPE)->count());
        static::$repository->deleteItem(new ItemUUID('75894379573', 'product'));
        self::$repository->flush();
        $this->assertSame(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, ElasticaWrapper::ITEM_TYPE)->count());
        static::$repository->deleteItem(new ItemUUID('5', 'product'));
        self::$repository->flush();
        $this->assertSame(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, ElasticaWrapper::ITEM_TYPE)->count());
        static::$repository->deleteItem(new ItemUUID('5', 'gum'));
        self::$repository->flush();
        $this->assertSame(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, ElasticaWrapper::ITEM_TYPE)->count());

        /**
         * Reseting scenario for next calls.
         */
        self::resetScenario();
    }
}
