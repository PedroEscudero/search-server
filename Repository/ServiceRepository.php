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

namespace Mmoreram\SearchBundle\Repository;

use Mmoreram\SearchBundle\Core\IndexRepository;
use Mmoreram\SearchBundle\Core\QueryRepository;
use Mmoreram\SearchBundle\Model\Brand;
use Mmoreram\SearchBundle\Model\Category;
use Mmoreram\SearchBundle\Model\Manufacturer;
use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Tag;
use Mmoreram\SearchBundle\Query\Query;
use Mmoreram\SearchBundle\Result\Result;

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
