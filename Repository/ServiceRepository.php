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

namespace Puntmig\Search\Server\Repository;

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Repository\Repository;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Core\IndexRepository;
use Puntmig\Search\Server\Core\QueryRepository;

/**
 * Class ServiceRepository.
 */
class ServiceRepository extends Repository
{
    /**
     * @var QueryRepository
     *
     * Query repository
     */
    private $queryRepository;

    /**
     * @var IndexRepository
     *
     * Index repository
     */
    private $indexRepository;

    /**
     * ServiceRepository constructor.
     *
     * @param QueryRepository $queryRepository
     * @param IndexRepository $indexRepository
     */
    public function __construct(
        QueryRepository $queryRepository,
        IndexRepository $indexRepository
    ) {
        parent::__construct();

        $this->queryRepository = $queryRepository;
        $this->indexRepository = $indexRepository;
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey(string $key)
    {
        parent::setKey($key);

        $this
            ->indexRepository
            ->setKey($key);
    }

    /**
     * Flush products.
     *
     * @param Product[] $products
     */
    protected function flushProducts(array $products)
    {
        $this
            ->indexRepository
            ->addProducts($products);
    }

    /**
     * Flush categories.
     *
     * @param Category[] $categories
     */
    protected function flushCategories(array $categories)
    {
        $this
            ->indexRepository
            ->addCategories($categories);
    }

    /**
     * Flush manufacturers.
     *
     * @param Manufacturer[] $manufacturers
     */
    protected function flushManufacturers(array $manufacturers)
    {
        $this
            ->indexRepository
            ->addManufacturers($manufacturers);
    }

    /**
     * Flush brands.
     *
     * @param Brand[] $brands
     */
    protected function flushBrands(array $brands)
    {
        $this
            ->indexRepository
            ->addBrands($brands);
    }

    /**
     * Flush tags.
     *
     * @param Tag[] $tags
     */
    protected function flushTags(array $tags)
    {
        $this
            ->indexRepository
            ->addTags($tags);
    }

    /**
     * Search cross the index types.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function query(Query $query) : Result
    {
        return $this
            ->queryRepository
            ->query(
                $this->getKey(),
                $query
            );
    }
}
