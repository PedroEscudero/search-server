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

use Puntmig\Search\Model\ManufacturerReference;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\ProductReference;
use Puntmig\Search\Query\Query;

/**
 * Class ExcludeReferencesTest.
 */
trait ExcludeReferencesTest
{
    /**
     * Test family filter.
     */
    public function testExcludeProducts()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->excludeReference(new ProductReference('2', 'product'))),
            Product::TYPE,
            ['?1', '!2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->excludeReferences([
                new ProductReference('2', 'product'),
                new ProductReference('3', 'book'),
                new ProductReference('4', 'superbike'),
                new ProductReference('6', 'boke'),
            ])),
            Product::TYPE,
            ['?1', '!2', '!3', '?4', '?5']
        );

        $this->assertEmpty(
            $repository->query(Query::create('nike')->excludeReference(new ManufacturerReference('2')))->getManufacturers()
        );
    }
}
