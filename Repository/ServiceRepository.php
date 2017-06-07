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

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
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
     * Flush items.
     *
     * @param Item[]     $itemsToUpdate
     * @param ItemUUID[] $itemsToDelete
     */
    protected function flushItems(
        array $itemsToUpdate,
        array $itemsToDelete
    ) {
        if (!empty($itemsToUpdate)) {
            $this
                ->indexRepository
                ->addItems($itemsToUpdate);
        }

        if (!empty($itemsToDelete)) {
            $this
                ->deleteRepository
                ->deleteItems($itemsToDelete);
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
