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

use Apisearch\Config\Config;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Query;
use Apisearch\Repository\Repository as BaseRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Result\Result;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;

/**
 * Class Repository.
 */
class Repository extends BaseRepository
{
    /**
     * @var ElasticaWrapperWithRepositoryReference[]
     *
     * Repositories
     */
    private $repositories = [];

    /**
     * Add repository.
     *
     * @param ElasticaWrapperWithRepositoryReference $repository
     */
    public function addRepository(ElasticaWrapperWithRepositoryReference $repository)
    {
        $this->repositories[] = $repository;
    }

    /**
     * Get repository by class.
     *
     * @param string $class
     *
     * @return ElasticaWrapperWithRepositoryReference
     */
    private function getRepository(string $class)
    {
        foreach ($this->repositories as $repository) {
            if (get_class($repository) === $class) {
                return $repository;
            }
        }
    }

    /**
     * Set repository reference.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function setRepositoryReference(RepositoryReference $repositoryReference)
    {
        parent::setRepositoryReference($repositoryReference);

        foreach ($this->repositories as $repository) {
            $repository->setRepositoryReference($repositoryReference);
        }
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
                ->getRepository(IndexRepository::class)
                ->addItems($itemsToUpdate);
        }

        if (!empty($itemsToDelete)) {
            $this
                ->getRepository(DeleteRepository::class)
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
            ->getRepository(QueryRepository::class)
            ->query($query);
    }

    /**
     * Create an index.
     *
     * @throws ResourceExistsException
     */
    public function createIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
            ->createIndex();
    }

    /**
     * Delete an index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        $this
            ->getRepository(IndexRepository::class)
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
            ->getRepository(IndexRepository::class)
            ->resetIndex();
    }

    /**
     * Config the index.
     *
     * @param Config $config
     *
     * @throws ResourceNotAvailableException
     */
    public function configureIndex(Config $config)
    {
        $this
            ->getRepository(ConfigRepository::class)
            ->configureIndex($config);
    }
}
