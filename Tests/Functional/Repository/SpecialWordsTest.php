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

use Puntmig\Search\Query\Query;

/**
 * Class SpecialWordsTest.
 */
trait SpecialWordsTest
{
    /**
     * Test metadata.
     */
    public function testSpecialWords()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::create('Vinci'))->getProducts()[0];
        $this->assertEquals(
            5,
            $product->getId()
        );

        $product = $repository->query(Query::create('vinci'))->getProducts()[0];
        $this->assertEquals(
            5,
            $product->getId()
        );

        $product = $repository->query(Query::create('vinc'))->getProducts()[0];
        $this->assertEquals(
            3,
            $product->getId()
        );

        $product = $repository->query(Query::create('da vinci'))->getProducts()[0];
        $this->assertEquals(
            3,
            $product->getId()
        );

        $product = $repository->query(Query::create('engonga'))->getProducts()[0];
        $this->assertEquals(
            3,
            $product->getId()
        );
    }
}
