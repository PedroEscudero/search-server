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
use Puntmig\Search\Model\BrandReference;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\CategoryReference;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\ManufacturerReference;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\ProductReference;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Model\TagReference;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Repository\Repository;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Core\DeleteRepository;
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
     * @var DeleteRepository
     *
     * Delete repository
     */
    private $deleteRepository;

    /**
     * ServiceRepository constructor.
     *
     * @param QueryRepository  $queryRepository
     * @param IndexRepository  $indexRepository
     * @param DeleteRepository $deleteRepository
     */
    public function __construct(
        QueryRepository $queryRepository,
        IndexRepository $indexRepository,
        DeleteRepository $deleteRepository
    ) {
        parent::__construct();

        $this->queryRepository = $queryRepository;
        $this->indexRepository = $indexRepository;
        $this->deleteRepository = $deleteRepository;
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey(string $key)
    {
        parent::setKey($key);

        $this->queryRepository->setKey($key);
        $this->indexRepository->setKey($key);
        $this->deleteRepository->setKey($key);
    }

    /**
     * Flush products.
     *
     * @param Product[]          $productsToUpdate
     * @param ProductReference[] $productsToDelete
     */
    protected function flushProducts(
        array $productsToUpdate,
        array $productsToDelete
    ) {
        if (!empty($productsToUpdate)) {
            $this
                ->indexRepository
                ->addProducts($productsToUpdate);
        }

        if (!empty($productsToDelete)) {
            $this
                ->deleteRepository
                ->deleteProducts($productsToDelete);
        }
    }

    /**
     * Flush categories.
     *
     * @param Category[]          $categoriesToUpdate
     * @param CategoryReference[] $categoriesToDelete
     */
    protected function flushCategories(
        array $categoriesToUpdate,
        array $categoriesToDelete
    ) {
        if (!empty($categoriesToUpdate)) {
            $this
                ->indexRepository
                ->addCategories($categoriesToUpdate);
        }

        if (!empty($categoriesToDelete)) {
            $this
                ->deleteRepository
                ->deleteCategories($categoriesToDelete);
        }
    }

    /**
     * Flush manufacturers.
     *
     * @param Manufacturer[]          $manufacturersToUpdate
     * @param ManufacturerReference[] $manufacturersToDelete
     */
    protected function flushManufacturers(
        array $manufacturersToUpdate,
        array $manufacturersToDelete
    ) {
        if (!empty($manufacturersToUpdate)) {
            $this
                ->indexRepository
                ->addManufacturers($manufacturersToUpdate);
        }

        if (!empty($manufacturersToDelete)) {
            $this
                ->deleteRepository
                ->deleteManufacturers($manufacturersToDelete);
        }
    }

    /**
     * Flush brands.
     *
     * @param Brand[]          $brandsToUpdate
     * @param BrandReference[] $brandsToDelete
     */
    protected function flushBrands(
        array $brandsToUpdate,
        array $brandsToDelete
    ) {
        if (!empty($brandsToUpdate)) {
            $this
                ->indexRepository
                ->addBrands($brandsToUpdate);
        }

        if (!empty($brandsToDelete)) {
            $this
                ->deleteRepository
                ->deleteBrands($brandsToDelete);
        }
    }

    /**
     * Flush tags.
     *
     * @param Tag[]          $tagsToUpdate
     * @param TagReference[] $tagsToDelete
     */
    protected function flushTags(
        array $tagsToUpdate,
        array $tagsToDelete
    ) {
        if (!empty($tagsToUpdate)) {
            $this
                ->indexRepository
                ->addTags($tagsToUpdate);
        }

        if (!empty($tagsToDelete)) {
            $this
                ->deleteRepository
                ->deleteTags($tagsToDelete);
        }
    }

    /**
     * Search across the index types.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function query(Query $query) : Result
    {
        return $this
            ->queryRepository
            ->query($query);
    }

    /**
     * Reset the index.
     */
    public function reset()
    {
        $this
            ->indexRepository
            ->createIndex();
    }
}
