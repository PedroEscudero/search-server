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

use Mmoreram\SearchBundle\Model\Result;
use Mmoreram\SearchBundle\Query\Query;
use Symfony\Component\Yaml\Yaml;

use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Repository\SearchRepository;
use Mmoreram\SearchBundle\Tests\Functional\SearchBundleFunctionalTest;

/**
 * Class ElasticaSearchRepositoryTest.
 */
class ElasticaSearchRepositoryTest extends SearchBundleFunctionalTest
{
    /**
     * test Basic Population
     */
    public function testBasicPopulation()
    {
        $this->resetIndexAndGetRepository();

        $this->assertEquals(
            3,
            $this->get('search_bundle.elastica_wrapper')->getType('product')->count()
        );

        $this->assertEquals(
            5,
            $this->get('search_bundle.elastica_wrapper')->getType('category')->count()
        );

        $this->assertEquals(
            3,
            $this->get('search_bundle.elastica_wrapper')->getType('manufacturer')->count()
        );

        $this->assertEquals(
            3,
            $this->get('search_bundle.elastica_wrapper')->getType('brand')->count()
        );
    }
    /**
     * Test basic search.
     */
    public function testBasicSearch()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultCounts(
            $repository->search('000', Query::create('adidas'))
            , 1, 1, 1, 0
        );
        $this->assertResultCounts(
            $repository->search('001', Query::create('adidas'))
            , 0, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('ravioli'))
            , 0, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('book'))
            , 1, 1, 0, 0
        );
    }

    /**
     * Test family filter
     */
    public function testFamilyFilter()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultCounts(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['product']))
            , 1, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['book']))
            , 0, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['book', 'product']))
            , 1, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['book', 'product']))
            , 1, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('000', Query::create('awesome')->filterByFamilies(['book', 'product']))
            , 2, 0, 0, 0
        );
        $this->assertResultCounts(
            $repository->search('001', Query::create('adidas')->filterByFamilies(['book', 'product']))
            , 0, 0, 0, 0
        );
    }

    /**
     * Test category filter
     */
    public function testCategoryFilter()
    {
        $repository = $this->resetIndexAndGetRepository();
        $result = $repository->search('000', Query::create('adidas')->filterByCategories(['1']));
        $this->assertResultCounts($result, 1, 0, 0, 0);
        $result = $repository->search('000', Query::create('adidas')->filterByCategories(['99']));
        $this->assertResultCounts($result, 0, 0, 0, 0);
        $result = $repository->search('000', Query::create('adidas')->filterByCategories(['99', '1']));
        $this->assertResultCounts($result, 1, 0, 0, 0);
        $result = $repository->search('000', Query::create('adidas')->filterByCategories(['2', '1']));
        $this->assertResultCounts($result, 1, 0, 0, 0);
        $result = $repository->search('000', Query::create('nike')->filterByCategories(['2']));
        $this->assertResultCounts($result, 0, 0, 0, 0);
        $result = $repository->search('000', Query::create('rebook')->filterByCategories(['1']));
        $this->assertResultCounts($result, 0, 0, 0, 0);
    }

    /**
     * Test filter by
     */

    /**
     * Reset index
     *
     * @return SearchRepository
     */
    private function resetIndexAndGetRepository()
    {
        $this->get('search_bundle.elastica_wrapper')->createIndexMapping();
        $repository = $this->get('search_bundle.elastica_repository');
        $products = Yaml::parse(file_get_contents(__DIR__ . '/../../basic_catalog.yml'));
        foreach ($products['products'] as $product) {
            $repository->index('000', Product::createFromArray($product));
        }

        return $repository;
    }

    /**
     * Assert nb items
     *
     * @param Result $result
     * @param int $nbProducts
     * @param int $nbCategories
     * @param int $nbManufacturers
     * @param int $nbBrands
     */
    private function assertResultCounts
    (
        Result $result,
        int $nbProducts,
        int $nbCategories,
        int $nbManufacturers,
        int $nbBrands
    ) {
        $this->assertCount($nbProducts, $result->getProducts());
        $this->assertCount($nbCategories, $result->getManufacturers());
        $this->assertCount($nbManufacturers, $result->getBrands());
        $this->assertCount($nbBrands, $result->getCategories());
    }
}
