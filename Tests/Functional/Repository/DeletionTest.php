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

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;

/**
 * Class DeletionTest.
 */
trait DeletionTest
{
    /**
     * Test product deletions.
     */
    public function testProductDeletions()
    {
        static::$repository->removeProduct('1');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->removeProduct('1');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->removeProduct('75894379573');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->removeProduct('5');
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
    }

    /**
     * Test category deletions.
     */
    public function testCategoryDeletions()
    {
        static::$repository->removeCategory('1');
        static::$repository->removeCategory('777');
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->removeCategory('777');
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->removeCategory('5498757698375');
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->removeCategory('778');
        self::$repository->flush();
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
    }

    /**
     * Test manufacturer deletions.
     */
    public function testManufacturerDeletions()
    {
        static::$repository->removeManufacturer('3');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->removeManufacturer('3');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->removeManufacturer('758493759834759');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->removeManufacturer('15');
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
    }

    /**
     * Test brand deletions.
     */
    public function testBrandDeletions()
    {
        static::$repository->removeBrand('3');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->removeBrand('3');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->removeBrand('758493759834759');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->removeBrand('10');
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
    }

    /**
     * Test tag deletions.
     */
    public function testTagDeletions()
    {
        static::$repository->removeTag('kids');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->removeTag('kids');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->removeTag('non-existent-tag');
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->removeTag('new');
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());

        /**
         * Reseting scenario for next calls.
         */
        self::resetScenario();
    }
}
