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
     * Test indexed metadata.
     */
    public function testIndexedMetadata()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::createMatchAll()->filterBy('id', ['1'], Filter::MUST_ALL))->getProducts()[0];
        $metadata = $product->getIndexedMetadata();

        $this->assertSame(
            'This is my field one',
            $metadata['field_text']
        );

        $this->assertSame(
            'my_keyword',
            $metadata['field_keyword']
        );

        $this->assertSame(
            '1',
            $metadata['field_boolean']
        );

        $this->assertSame(
            '10',
            $metadata['field_integer']
        );

        $this->assertSame('This is my field one', $product->getIndexedMetadataValue('field_text'));
        $this->assertSame('10', $product->getIndexedMetadataValue('field_integer'));
        $this->assertSame('This is my field one', $product->getAllMetadataValue('field_text'));
        $this->assertSame('10', $product->getAllMetadataValue('field_integer'));
    }

    /**
     * Test metadata.
     */
    public function testMetadata()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::createMatchAll()->filterBy('id', ['5'], Filter::MUST_ALL))->getProducts()[0];
        $metadata = $product->getMetadata();
        $this->assertSame(
            'value1',
            $metadata['field1']
        );

        $this->assertSame(
            10,
            $metadata['field2']
        );

        $this->assertSame('value1', $product->getMetadataValue('field1'));
        $this->assertSame(10, $product->getMetadataValue('field2'));
        $this->assertSame('value1', $product->getAllMetadataValue('field1'));
        $this->assertSame(10, $product->getAllMetadataValue('field2'));
    }

    /**
     * Test empty metadata.
     */
    public function testEmptyMetadata()
    {
        $repository = static::$repository;
        $product = $repository->query(Query::createMatchAll()->filterBy('id', ['2'], Filter::MUST_ALL))->getProducts()[0];
        $this->assertEmpty($product->getMetadata());
    }

    /**
     * Test default with_discount metadata value and aggregations.
     */
    public function testWithDiscountMetadata()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createMatchAll()->filterByMeta('with_discount', ['1'], Filter::AT_LEAST_ONE));
        $firstResult = $result->getFirstResult();
        $withDiscountAggregation = $result->getMetaAggregation('with_discount');
        $this->assertEquals(2, $withDiscountAggregation->getCounter('0')->getN());
        $this->assertEquals(3, $withDiscountAggregation->getCounter('1')->getN());
        $this->assertEquals('1', $firstResult->getIndexedMetadata()['with_discount']);
    }

    /**
     * Test default with_image metadata value and aggregations.
     */
    public function testWithImageMetadata()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createMatchAll()->filterByMeta('with_image', ['1'], Filter::AT_LEAST_ONE));
        $firstResult = $result->getFirstResult();
        $withDiscountAggregation = $result->getMetaAggregation('with_image');
        $this->assertEquals(3, $withDiscountAggregation->getCounter('0')->getN());
        $this->assertEquals(2, $withDiscountAggregation->getCounter('1')->getN());
        $this->assertEquals('1', $firstResult->getIndexedMetadata()['with_image']);
        $this->assertNotEmpty($firstResult->getImage());
    }

    /**
     * Test default with_stock metadata value and aggregations.
     */
    public function testWithStockMetadata()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createMatchAll()->filterByMeta('with_stock', ['0'], Filter::AT_LEAST_ONE));
        $firstResult = $result->getFirstResult();
        $withDiscountAggregation = $result->getMetaAggregation('with_stock');
        $this->assertEquals(2, $withDiscountAggregation->getCounter('0')->getN());
        $this->assertEquals(3, $withDiscountAggregation->getCounter('1')->getN());
        $this->assertEquals('0', $firstResult->getIndexedMetadata()['with_stock']);
        $this->assertTrue($firstResult->getStock() <= 0);
    }

    /**
     * Test with indexed metadata when using arrays of strings.
     */
    public function testWithArrayIndexedMetadata()
    {
        $repository = static::$repository;
        $result = $repository->query(Query::createMatchAll()->filterByMeta('color', [], Filter::AT_LEAST_ONE));
        $aggregations = $result->getAggregations();
        $metaAggregation = $aggregations->getMetaAggregation('color');
        $this->assertEquals(2, $metaAggregation->getCounter('yellow')->getN());
        $this->assertEquals(1, $metaAggregation->getCounter('blue')->getN());
        $this->assertEquals(1, $metaAggregation->getCounter('red')->getN());
    }
}
