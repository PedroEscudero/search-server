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
use Puntmig\Search\Model\ManufacturerReference;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\ProductReference;
use Puntmig\Search\Query\Query;

/**
 * Class SearchTest.
 */
trait SearchTest
{
    /**
     * Test get match all.
     */
    public function testMatchAll()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createMatchAll());
        $this->assertSame(
            count($result->getProducts()),
            $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count()
        );
        $this->assertSame(
            count($result->getCategories()),
            $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count()
        );
        $this->assertSame(
            count($result->getManufacturers()),
            $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count()
        );
        $this->assertSame(
            count($result->getBrands()),
            $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count()
        );
    }

    /**
     * Test basic search.
     */
    public function testBasicSearch()
    {
        $repository = static::$repository;

        $result = $repository->query(Query::create('adidas'));
        $this->assertNTypeElementId($result, Product::TYPE, 0, '1');
        $this->assertNTypeElementId($result, Brand::TYPE, 0, '1');
        $this->assertNTypeElementId($result, Manufacturer::TYPE, 0, '1');
    }

    /**
     * Test basic search with all results method call.
     */
    public function testAllResults()
    {
        $repository = static::$repository;

        $results = $repository
            ->query(Query::create('adidas'))
            ->getResults();

        $this->assertCount(3, $results);
        $this->assertInstanceof(Manufacturer::class, $results[0]);
        $this->assertInstanceof(Brand::class, $results[1]);
        $this->assertInstanceof(Product::class, $results[2]);
    }

    /**
     * Test search by reference.
     */
    public function testSearchByReference()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createByReference(new ProductReference('1', 'product')));
        $this->assertCount(1, $result->getResults());
        $this->assertCount(1, $result->getProducts());
        $this->assertSame('1', $result->getProducts()[0]->getId());
    }

    /**
     * Test search by references.
     */
    public function testSearchByReferences()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createByReferences([
            new ProductReference('1', 'product'),
            new ManufacturerReference('1'),
        ]));
        $this->assertCount(2, $result->getResults());
        $this->assertCount(1, $result->getProducts());
        $this->assertCount(1, $result->getManufacturers());
        $this->assertSame('1', $result->getProducts()[0]->getId());
        $this->assertSame('1', $result->getManufacturers()[0]->getId());

        $repository = static::$repository;
        $result = $repository->query(Query::createByReferences([
            new ManufacturerReference('1'),
            new ManufacturerReference('1'),
        ]));
        $this->assertCount(1, $result->getResults());
        $this->assertCount(1, $result->getManufacturers());
    }
}
