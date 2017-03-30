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
use Puntmig\Search\Model\BrandReference;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\CategoryReference;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\ManufacturerReference;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\ProductReference;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Model\TagReference;

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
        static::$repository->deleteProduct(new ProductReference('1', 'product'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->deleteProduct(new ProductReference('1', 'product'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->deleteProduct(new ProductReference('75894379573', 'product'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->deleteProduct(new ProductReference('5', 'product'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        static::$repository->deleteProduct(new ProductReference('5', 'gum'));
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
    }

    /**
     * Test category deletions.
     */
    public function testCategoryDeletions()
    {
        static::$repository->deleteCategory(new CategoryReference('1'));
        static::$repository->deleteCategory(new CategoryReference('777'));
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->deleteCategory(new CategoryReference('777'));
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->deleteCategory(new CategoryReference('5498757698375'));
        self::$repository->flush();
        $this->assertEquals(6, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        static::$repository->deleteCategory(new CategoryReference('778'));
        self::$repository->flush();
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
    }

    /**
     * Test manufacturer deletions.
     */
    public function testManufacturerDeletions()
    {
        static::$repository->deleteManufacturer(new ManufacturerReference('3'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->deleteManufacturer(new ManufacturerReference('3'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->deleteManufacturer(new ManufacturerReference('396789789'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        static::$repository->deleteManufacturer(new ManufacturerReference('15'));
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
    }

    /**
     * Test brand deletions.
     */
    public function testBrandDeletions()
    {
        static::$repository->deleteBrand(new BrandReference('3'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->deleteBrand(new BrandReference('3'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->deleteBrand(new BrandReference('3698679879'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        static::$repository->deleteBrand(new BrandReference('10'));
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
    }

    /**
     * Test tag deletions.
     */
    public function testTagDeletions()
    {
        static::$repository->deleteTag(new TagReference('kids'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->deleteTag(new TagReference('kids'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->deleteTag(new TagReference('non-existing-tag'));
        self::$repository->flush();
        $this->assertEquals(4, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
        static::$repository->deleteTag(new TagReference('new'));
        self::$repository->flush();
        $this->assertEquals(3, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());

        /**
         * Reseting scenario for next calls.
         */
        self::resetScenario();
    }
}
