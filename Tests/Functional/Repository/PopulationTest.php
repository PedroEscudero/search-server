<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Tests\Functional\Repository;

use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;

/**
 * Class PopulationTest.
 */
class PopulationTest extends ElasticaSearchRepositoryTest
{
    /**
     * test Basic Population.
     */
    public function testBasicPopulation()
    {
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Product::TYPE)->count());
        $this->assertEquals(8, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Category::TYPE)->count());
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Manufacturer::TYPE)->count());
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType(self::$key, Brand::TYPE)->count());
    }
}
