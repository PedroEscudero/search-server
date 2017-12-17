<?php

/*
 * This file is part of the Apisearch Server
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

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query;
use Apisearch\Repository\Repository as BaseRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;

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
     * Set repository reference.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function setRepositoryReference(RepositoryReference $repositoryReference)
    {
        parent::setRepositoryReference($repositoryReference);

        $this->queryRepository->setRepositoryReference($repositoryReference);
        $this->indexRepository->setRepositoryReference($repositoryReference);
        $this->deleteRepository->setRepositoryReference($repositoryReference);
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
     *
     * @throws ResourceNotAvailableException
     */
    public function query(Query $query): Result
    {
        return $this
            ->queryRepository
            ->query($query);
    }

    /**
     * Create an index.
     *
     * @param null|string $language
     *
     * @throws ResourceExistsException
     */
    public function createIndex(? string $language)
    {
        $this
            ->indexRepository
            ->createIndex($language);
    }

    /**
     * Delete an index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        $this
            ->indexRepository
            ->deleteIndex();
    }

    /**
     * Reset the index.
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex()
    {
        $this
            ->indexRepository
            ->resetIndex();
    }
}
