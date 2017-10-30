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

namespace Puntmig\Search\Server\Elastica\Repository;

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Repository\Repository as BaseRepository;
use Puntmig\Search\Result\Result;

/**
 * Class Repository.
 */
class Repository extends BaseRepository
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
     * @param string $appId
     */
    public function setAppId(string $appId)
    {
        parent::setAppId($appId);

        $this->queryRepository->setAppId($appId);
        $this->indexRepository->setAppId($appId);
        $this->deleteRepository->setAppId($appId);
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
    public function query(Query $query): Result
    {
        return $this
            ->queryRepository
            ->query($query);
    }

    /**
     * Reset the index.
     *
     * @var null|string
     */
    public function reset(? string $language)
    {
        $this
            ->indexRepository
            ->createIndex($language);
    }

    /**
     * Create the index.
     *
     * @param null|string $language
     */
    public function createIndex(? string $language)
    {
        $this->reset($language);
    }
}
