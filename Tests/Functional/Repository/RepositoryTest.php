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

use Symfony\Component\Yaml\Yaml;

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Repository\Repository;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Tests\Functional\PuntmigSearchServerBundleFunctionalTest;

/**
 * Class RepositoryTest.
 */
abstract class RepositoryTest extends PuntmigSearchServerBundleFunctionalTest
{
    use PopulationTest;
    use SearchTest;
    use FiltersTest;
    use LocationFiltersTest;
    use AggregationsTest;
    use SortTest;

    /**
     * @var Repository
     *
     * Repository
     */
    protected static $repository;

    /**
     * @var string
     *
     * Used api key
     */
    protected static $key = 'hjk45hj4k4';

    /**
     * @var string
     *
     * Another used api key
     */
    protected static $anotherKey = '5h43jk5h43';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::get('search_bundle.elastica_wrapper')->createIndexMapping(self::$key, 1);
        self::get('search_bundle.elastica_wrapper')->createIndexMapping(self::$anotherKey, 1);

        self::$repository = self::get(static::getRepositoryServiceName());
        self::$repository->setKey(self::$key);
        $products = Yaml::parse(file_get_contents(__DIR__ . '/../../basic_catalog.yml'));
        foreach ($products['products'] as $product) {
            self::$repository->addProduct(
                Product::createFromArray($product)
            );
        }

        self::$repository->flush(500);
    }

    /**
     * get repository service name.
     *
     * @return string
     */
    abstract protected static function getRepositoryServiceName() : string;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        self::$repository->setKey(self::$key);
    }

    /**
     * Assert IDS sequence.
     *
     * ["1", "2", "?3", "*4" "!999"]
     *
     * Positions are relative, and will be checked, so at this case we
     * are asserting that 1 and 2 exists, and 1 is scored higher than 2. We
     * assert as well that 999 is not a result
     *
     * An id with a ? before means that should only check that the element
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
    protected function assertResults(
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
            $mustCheckRelativity = (strpos($idWithoutGrouping, '?') !== 0) && $mustExist;
            $cleanId = trim($idWithoutGrouping, '?*!');

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
    protected function assertNTypeElementId(
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
    protected function assertId1MatchesBetterThanId2(
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
    protected function idFoundInResults(
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
    protected function getResultsByType(
        Result $result,
        string $type
    ): array {
        $elements = null;
        if ($type === Product::TYPE) {
            $elements = $result->getProducts();
        } elseif ($type === Category::TYPE) {
            $elements = $result->getCategories();
        } elseif ($type === Manufacturer::TYPE) {
            $elements = $result->getManufacturers();
        } elseif ($type === Brand::TYPE) {
            $elements = $result->getBrands();
        }

        if (is_null($elements)) {
            $this->fail("$type not defined properly");
        }

        return $elements;
    }
}
