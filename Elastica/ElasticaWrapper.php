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

namespace Apisearch\Server\Elastica;

use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;

/**
 * Class ElasticaWrapper.
 */
abstract class ElasticaWrapper
{
    /**
     * @var Client
     *
     * Elastica client
     */
    private $client;

    /**
     * Construct.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get item type
     *
     * @return string
     */
    public abstract function getItemType() : string;

    /**
     * Get index name
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return string
     */
    public abstract function getIndexName(RepositoryReference $repositoryReference): string;

    /**
     * Get index not available exception
     *
     * @param string $message
     *
     * @return ResourceNotAvailableException
     */
    public abstract function getIndexNotAvailableException(string $message) : ResourceNotAvailableException;

    /**
     * Get index configuration
     *
     * @param int $shards
     * @param int $replicas
     *
     * @return array
     */
    public abstract function getIndexConfiguration(
        int $shards,
        int $replicas
    ) : array;

    /**
     * Build index mapping
     *
     * @param Mapping $mapping
     */
    public abstract function buildIndexMapping(Mapping $mapping);

    /**
     * Get search index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return Index
     */
    public function getIndex(RepositoryReference $repositoryReference): Index
    {
        return $this
            ->client
            ->getIndex($this->getIndexName($repositoryReference));
    }

    /**
     * Delete index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex(RepositoryReference $repositoryReference)
    {
        try {
            $searchIndex = $this->getIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->delete();
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Remove index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex(RepositoryReference $repositoryReference)
    {
        try {
            $searchIndex = $this->getIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->deleteByQuery(new Query\MatchAll());
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param int                 $shards
     * @param int                 $replicas
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        RepositoryReference $repositoryReference,
        int $shards,
        int $replicas
    ) {
        $searchIndex = $this->getIndex($repositoryReference);

        try {
            $searchIndex->create($this->getIndexConfiguration(
                $shards,
                $replicas
            ));
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param string              $typeName
     *
     * @return Type
     */
    public function getType(
        RepositoryReference $repositoryReference,
        string $typeName
    ) {
        return $this
            ->getIndex($repositoryReference)
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param RepositoryReference $repositoryReference
     * @param Query               $query
     * @param int                 $from
     * @param int                 $size
     *
     * @return array
     */
    public function search(
        RepositoryReference $repositoryReference,
        Query $query,
        int $from,
        int $size
    ): array {
        try {
            $queryResult = $this
                ->getIndex($repositoryReference)
                ->search($query, [
                    'from' => $from,
                    'size' => $size,
                ]);
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }

        return [
            'results' => $queryResult->getResults(),
            'suggests' => $queryResult->getSuggests(),
            'aggregations' => $queryResult->getAggregations(),
            'total_hits' => $queryResult->getTotalHits(),
        ];
    }

    /**
     * Refresh.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function refresh(RepositoryReference $repositoryReference)
    {
        $this
            ->getIndex($repositoryReference)
            ->refresh();
    }

    /**
     * Create mapping.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceExistsException
     */
    public function createIndexMapping(RepositoryReference $repositoryReference)
    {
        try {
            $itemMapping = new Mapping();
            $itemMapping->setType($this->getType($repositoryReference, $this->getItemType()));
            $this->buildIndexMapping($itemMapping);
            $itemMapping->send();
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Add documents.
     *
     * @param RepositoryReference $repositoryReference
     * @param Document[]          $documents
     *
     * @throws ResourceExistsException
     */
    public function addDocuments(
        RepositoryReference $repositoryReference,
        array $documents
    ) {
        try {
            $this
                ->getType($repositoryReference, $this->getItemType())
                ->addDocuments($documents);
        } catch (BulkResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }

    /**
     * Delete documents by its.
     *
     * @param RepositoryReference $repositoryReference
     * @param string[]            $documentsId
     *
     * @throws ResourceExistsException
     */
    public function deleteDocumentsByIds(
        RepositoryReference $repositoryReference,
        array $documentsId
    ) {
        try {
            $this
                ->getType($repositoryReference, $this->getItemType())
                ->deleteIds($documentsId);
        } catch (BulkResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw $this->getIndexNotAvailableException($exception->getMessage());
        }
    }
}
