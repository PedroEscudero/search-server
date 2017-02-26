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

use Symfony\Component\Yaml\Yaml;

use Mmoreram\SearchBundle\Model\Model;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Result;
use Mmoreram\SearchBundle\Query\PriceRange;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Query\SortBy;
use Mmoreram\SearchBundle\Repository\SearchRepository;
use Mmoreram\SearchBundle\Tests\Functional\SearchBundleFunctionalTest;

/**
 * Class ElasticaSearchRepositoryTest.
 */
class ElasticaSearchRepositoryTest extends SearchBundleFunctionalTest
{
    /**
     * test Basic Population.
     */
    public function testBasicPopulation()
    {
        $this->resetIndexAndGetRepository();

        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType('product')->count());
        $this->assertEquals(8, $this->get('search_bundle.elastica_wrapper')->getType('category')->count());
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType('manufacturer')->count());
        $this->assertEquals(5, $this->get('search_bundle.elastica_wrapper')->getType('brand')->count());
    }

    /**
     * Test get match all.
     */
    public function testMatchAll()
    {
        $repository = $this->resetIndexAndGetRepository();
        $result = $repository->search('000', Query::createMatchAll());
        $this->assertEquals(
            count($result->getProducts()),
            $this->get('search_bundle.elastica_wrapper')->getType('product')->count()
        );
        $this->assertEquals(
            count($result->getCategories()),
            $this->get('search_bundle.elastica_wrapper')->getType('category')->count()
        );
        $this->assertEquals(
            count($result->getManufacturers()),
            $this->get('search_bundle.elastica_wrapper')->getType('manufacturer')->count()
        );
        $this->assertEquals(
            count($result->getBrands()),
            $this->get('search_bundle.elastica_wrapper')->getType('brand')->count()
        );
    }

    /**
     * Test basic search.
     */
    public function testBasicSearch()
    {
        $repository = $this->resetIndexAndGetRepository();

        $result = $repository->search('000', Query::create('adidas'));
        $this->assertNTypeElementId($result, Model::PRODUCT, 0, '1');
        $this->assertNTypeElementId($result, Model::BRAND, 0, '1');
        $this->assertNTypeElementId($result, Model::MANUFACTURER, 0, '1');
    }

    /**
     * Test family filter.
     */
    public function testFamilyFilter()
    {
        $repository = $this->resetIndexAndGetRepository();

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['product'])),
            Model::PRODUCT,
            ['1', '{*2', '*3}']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('da vinci')->filterByFamilies(['book'])),
            Model::PRODUCT,
            ['3', '{*1', '*2}']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('da vinci')->filterByFamilies(['book', 'products'])),
            Model::PRODUCT,
            ['3', '{*1', '*2}']
        );

        $this->assertEmpty(
            $repository->search('000', Query::create('adidas')->filterByFamilies(['nonexistent']))->getProducts()
        );
    }

    /**
     * Test category filter.
     */
    public function testCategoryFilter()
    {
        $repository = $this->resetIndexAndGetRepository();

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByCategories(['1'])),
            Model::PRODUCT,
            ['1', '2', '!3']
        );

        $this->assertEmpty(
            $repository->search('000', Query::create('adidas')->filterByCategories(['_99']))->getProducts()
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByCategories(['1', '_4578943'])),
            Model::PRODUCT,
            ['1', '2', '!3']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByCategories(['2', '3'])),
            Model::PRODUCT,
            ['1', '2', '!3']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByCategories(['3'])),
            Model::PRODUCT,
            ['2', '!1', '!3']
        );
    }

    /**
     * Test filter by manufacturer.
     */
    public function testManufacturerFilter()
    {
        $repository = $this->resetIndexAndGetRepository();

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByManufacturer('1')),
            Model::PRODUCT,
            ['1', '!2', '!3']
        );

        $this->assertEmpty(
             $repository->search('000', Query::create('shirt')->filterByManufacturer('_4543543'))->getProducts()
        );
    }

    /**
     * Test filter by brand.
     */
    public function testBrandFilter()
    {
        $repository = $this->resetIndexAndGetRepository();

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('adidas')->filterByBrand('1')),
            Model::PRODUCT,
            ['1', '!2', '!3']
        );

        $this->assertEmpty(
             $repository->search('000', Query::create('shirt')->filterByManufacturer('_4543543'))->getProducts()
        );
    }

    /**
     * Test filter by price range.
     */
    public function testPriceRangeFilter()
    {
        $repository = $this->resetIndexAndGetRepository();

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('Dan Brown')->filterByPriceRange(1000, 2000)),
            Model::PRODUCT,
            ['3', '2', '!1']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::create('Dan Brown')->filterByPriceRange(1000, 2000)->filterByFamilies(['book'])),
            Model::PRODUCT,
            ['3', '!2', '!1']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(900, 1900)),
            Model::PRODUCT,
            ['#1', '#2', '!3']
        );

        $this->assertEmpty(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(100, 200))->getProducts()
        );

        $this->assertEmpty(
            $repository->search('001', Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::INFINITE))->getProducts()
        );

        $this->assertEmpty(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::FREE))->getProducts()
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::INFINITE)),
            Model::PRODUCT,
            ['#1', '#2', '#3']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(1, PriceRange::INFINITE)),
            Model::PRODUCT,
            ['#1', '#2', '#3']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(PriceRange::FREE, PriceRange::FREE)->removeFilterByPriceRange()),
            Model::PRODUCT,
            ['#1', '#2', '#3']
        );
    }

    /**
     * Test sort by price asc.
     */
    public function testSortByPriceAsc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::PRICE_ASC)),
            Model::PRODUCT,
            ['1', '2', '3']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(900, 1900)->sortBy(SortBy::PRICE_ASC)),
            Model::PRODUCT,
            ['1', '2', '!3']
        );
    }

    /**
     * Test sort by price desc.
     */
    public function testSortByPriceDesc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::PRICE_DESC)),
            Model::PRODUCT,
            ['3', '2', '1']
        );

        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->filterByPriceRange(900, 1900)->sortBy(SortBy::PRICE_DESC)),
            Model::PRODUCT,
            ['2', '1', '!3']
        );
    }

    /**
     * Test sort by discount ASC.
     */
    public function testSortByDiscountAsc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::DISCOUNT_ASC)),
            Model::PRODUCT,
            ['{2', '3}', '5', '1', '4']
        );
    }

    /**
     * Test sort by discount DESC.
     */
    public function testSortByDiscountDesc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::DISCOUNT_DESC)),
            Model::PRODUCT,
            ['4', '1', '5', '{2', '3}']
        );
    }

    /**
     * Test sort by discount percentage ASC.
     */
    public function testSortByDiscountPercentageAsc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::DISCOUNT_PERCENTAGE_ASC)),
            Model::PRODUCT,
            ['{2', '3}', '1', '5', '4']
        );
    }

    /**
     * Test sort by discount percentage DESC.
     */
    public function testSortByDiscountPercentageDesc()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::DISCOUNT_PERCENTAGE_DESC)),
            Model::PRODUCT,
            ['4', '5', '1', '{2', '3}']
        );
    }

    /**
     * Test sort by manufacturer asc.
     */
    public function testSortByManufacturerASC()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::MANUFACTURER_ASC)),
            Model::PRODUCT,
            ['1', '3', '2']
        );
    }

    /**
     * Test sort by manufacturer desc.
     */
    public function testSortByManufacturerDESC()
    {
        $repository = $this->resetIndexAndGetRepository();
        $this->assertResultsRelativePositions(
            $repository->search('000', Query::createMatchAll()->sortBy(SortBy::MANUFACTURER_DESC)),
            Model::PRODUCT,
            ['2', '3', '1']
        );
    }

    /**
     * Reset index.
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
     * Assert IDS sequence.
     *
     * ["1", "2", "#3", "*4" "!999"]
     *
     * Positions are relative, and will be checked, so at this case we
     * are asserting that 1 and 2 exists, and 1 is scored higher than 2. We
     * assert as well that 999 is not a result
     *
     * An id with a # before means that should only check that the element
     * exists, but the relativity is not checked
     *
     * An id with a * before means that should only check relativity and not
     * element existence
     *
     * Grouping can also be managed by using { and }
     *
     * ["1", "{2", "3}", "5"]
     *
     * This means that will be checked
     *
     * * 1 > 2
     * * 1 > 3
     * * 3 > 5
     *
     * Only one group nesting is allowing
     *
     * @param Result   $result
     * @param string   $type
     * @param string[] $ids
     */
    private function assertResultsRelativePositions(
        Result $result,
        string $type,
        array $ids
    ) {
        $lastIdFound = false;
        $inGroup = false;
        foreach ($ids as $id) {
            $idWithoutGrouping = trim($id, '{}');
            $mustCheckExistence = strpos($idWithoutGrouping, '*') !== 0;
            $mustExist = strpos($idWithoutGrouping, '!') !== 0;
            $mustCheckRelativity = (strpos($idWithoutGrouping, '#') !== 0) && $mustExist;
            $cleanId = trim($idWithoutGrouping, '#*!');

            if ($mustCheckExistence) {
                $this->assertEquals(
                    $mustExist,
                    $this->idFoundInResults($result, $type, $cleanId)
                );
            }

            if (
                $mustCheckRelativity &&
                is_string($lastIdFound)
            ) {
                $this->assertId1MatchesBetterThanId2(
                    $result,
                    $type,
                    $lastIdFound,
                    $cleanId
                );
            }

            if (strlen($id) !== strlen(ltrim($id, '{'))) {
                $inGroup = true;
            }

            if (strlen($id) !== strlen(rtrim($id, '}'))) {
                $inGroup = false;
            }

            if (!$inGroup) {
                $lastIdFound = $cleanId;
            }
        }
    }

    /**
     * Assert that the position *n* contains the type element desired, and the
     * id specified.
     *
     * If position is null, will assert if the entry does not exists
     *
     * @param Result $result
     * @param string $type
     * @param int    $position
     * @param string $id
     */
    private function assertNTypeElementId(
        Result $result,
        string $type,
        int $position,
        string $id
    ) {
        $elements = $this->getResultsByType(
            $result,
            $type
        );

        if (!array_key_exists($position, $elements)) {
            $this->fail("Element $position not found in results stack for type $type");
        } else {
            $this->assertEquals(
                $id,
                $elements[$position]->getId()
            );
        }
    }

    /**
     * Assert that id1 matches better than id2 in a result, given a result set
     * and a type.
     *
     * @param Result $result
     * @param string $type
     * @param string $id1
     * @param string $id2
     */
    private function assertId1MatchesBetterThanId2(
        Result $result,
        string $type,
        string $id1,
        string $id2
    ) {
        $elements = $this->getResultsByType(
            $result,
            $type
        );

        $foundId1 = false;
        foreach ($elements as $element) {
            $foundId = $element->getId();
            if ($id1 === $foundId) {
                $foundId1 = true;
                continue;
            }

            if ($id2 === $foundId) {
                $this->assertTrue($foundId1, "$type $id2 was not found after $type $id1");

                return;
            }
        }

        $this->assertTrue($foundId1, "$type $id2 was not found after $type $id1");
    }

    /**
     * Id is found in results of type.
     *
     * @param Result $result
     * @param string $type
     * @param string $id
     *
     * @return bool
     */
    private function idFoundInResults(
        Result $result,
        string $type,
        string $id
    ) : bool {
        $elements = $this->getResultsByType(
            $result,
            $type
        );
        $found = false;
        foreach ($elements as $element) {
            if ($element->getId() === $id) {
                $found = true;
            }
        }

        return $found;
    }

    /**
     * Get result results set by type.
     *
     * @param Result $result
     * @param string $type
     *
     * @return array
     */
    private function getResultsByType(
        Result $result,
        string $type
    ): array {
        $elements = null;
        if ($type === Model::PRODUCT) {
            $elements = $result->getProducts();
        } elseif ($type === Model::CATEGORY) {
            $elements = $result->getCategories();
        } elseif ($type === Model::MANUFACTURER) {
            $elements = $result->getManufacturers();
        } elseif ($type === Model::BRAND) {
            $elements = $result->getBrands();
        }

        if (is_null($elements)) {
            $this->fail("$type not defined properly");
        }

        return $elements;
    }
}
