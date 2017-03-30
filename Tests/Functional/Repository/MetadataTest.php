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

use Puntmig\Search\Query\Filter;
use Puntmig\Search\Query\Query;

/**
 * Class MetadataTest.
 */
trait MetadataTest
{
    /**
     * Test metadata.
     */
    public function testMetadata()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::createMatchAll()->filterBy('id', ['1'], Filter::MUST_ALL))->getProducts()[0];
        $metadata = $product->getMetadata();
        $this->assertEquals(
            'This is my field one',
            $metadata['field_text']
        );

        $this->assertEquals(
            'my_keyword',
            $metadata['field_keyword']
        );

        $this->assertEquals(
            true,
            $metadata['field_boolean']
        );

        $this->assertEquals(
            '10',
            $metadata['field_integer']
        );
    }

    /**
     * Test metadata.
     */
    public function testEmptyMetadata()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::createMatchAll()->filterBy('id', ['2'], Filter::MUST_ALL))->getProducts()[0];
        $this->assertEmpty($product->getMetadata());
    }
}
