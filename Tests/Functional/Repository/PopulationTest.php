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
 * Class PopulationTest.
 */
trait PopulationTest
{
    /**
     * test Basic Population.
     */
    public function testBasicPopulation()
    {
        $this->assertSame(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        $this->assertSame(8, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        $this->assertSame(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        $this->assertSame(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
        $this->assertSame(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Tag::TYPE)->count());
    }
}
