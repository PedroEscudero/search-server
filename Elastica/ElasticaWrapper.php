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
use Elastica\Exception\ResponseException;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;

/**
 * Class ElasticaWrapper.
 */
class ElasticaWrapper
{
    /**
     * @var string
     *
     * Item type
     */
    const ITEM_TYPE = 'item';

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
     * Get search index.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return Index
     */
    public function getSearchIndex(RepositoryReference $repositoryReference): Index
    {
        return $this
            ->client
            ->getIndex('apisearch_' . $repositoryReference->compose());
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
            $searchIndex = $this->getSearchIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->delete();

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }
    }

    /**
     * Remove index
     *
     * @param RepositoryReference $repositoryReference
     *
     * @throws ResourceNotAvailableException
     */
    public function resetIndex(RepositoryReference $repositoryReference)
    {
        try {
            $searchIndex = $this->getSearchIndex($repositoryReference);
            $searchIndex->clearCache();
            $searchIndex->deleteByQuery(new Query\MatchAll());

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param int                 $shards
     * @param int                 $replicas
     * @param null|string         $language
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        RepositoryReference $repositoryReference,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $searchIndex = $this->getSearchIndex($repositoryReference);
        $indexConfiguration = [
            'number_of_shards' => $shards,
            'number_of_replicas' => $replicas,
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'asciifolding',
                            'ngram_filter',
                            'stop_words',
                        ],
                    ],
                    'search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'asciifolding',
                            'stop_words',
                        ],
                    ],
                ],
                'filter' => [
                    'ngram_filter' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 1,
                        'max_gram' => 20,
                        'token_chars' => [
                            'letter',
                        ],
                    ],
                    'stop_words' => [
                        'type' => 'stop',
                        'stopwords' => ElasticaLanguages::getStopwordsLanguageByIso($language),
                    ],
                ],
                'normalizer' => [
                    'exact_matching_normalizer' => [
                        'type' => 'custom',
                        'filter' => [
                            'lowercase',
                            'asciifolding',
                        ],
                    ],
                ],
            ],
        ];

        $stemmer = ElasticaLanguages::getStemmerLanguageByIso($language);
        if (!is_null($stemmer)) {
            $indexConfiguration['analysis']['analyzer']['default']['filter'][] = 'stemmer';
            $indexConfiguration['analysis']['analyzer']['search_analyzer']['filter'][] = 'stemmer';
            $indexConfiguration['analysis']['filter']['stemmer'] = [
                'type' => 'stemmer',
                'name' => $stemmer,
            ];
        }

        try {
            $searchIndex->create($indexConfiguration);
        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceExistsException::indexExists();
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
            ->getSearchIndex($repositoryReference)
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
    ) : array {
        try {
            $queryResult = $this
                ->getSearchIndex($repositoryReference)
                ->search($query, [
                    'from' => $from,
                    'size' => $size,
                ]);

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }

        return [
            'items' => $queryResult->getResults(),
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
            ->getSearchIndex($repositoryReference)
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
            $itemMapping->setType($this->getType($repositoryReference, self::ITEM_TYPE));
            $itemMapping->setParam('dynamic_templates', [
                [
                    'dynamic_metadata_as_keywords' => [
                        'path_match'         => 'indexed_metadata.*',
                        'match_mapping_type' => 'string',
                        'mapping'            => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                [
                    'dynamic_searchable_metadata_as_text' => [
                        'path_match' => 'searchable_metadata.*',
                        'mapping'    => [
                            'type'            => 'text',
                            'analyzer'        => 'default',
                            'search_analyzer' => 'search_analyzer',
                        ],
                    ],
                ],
            ]);
            $itemMapping->setProperties([
                'uuid'                    => [
                    'type'       => 'object',
                    'dynamic'    => 'strict',
                    'properties' => [
                        'id'   => [
                            'type' => 'keyword',
                        ],
                        'type' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
                'coordinate'              => ['type' => 'geo_point'],
                'metadata'                => [
                    'type'    => 'object',
                    'dynamic' => true,
                    'enabled' => false,
                ],
                'indexed_metadata'        => [
                    'type'    => 'object',
                    'dynamic' => true,
                ],
                'searchable_metadata'     => [
                    'type'    => 'object',
                    'dynamic' => true,
                ],
                'exact_matching_metadata' => [
                    'type'       => 'keyword',
                    'normalizer' => 'exact_matching_normalizer',
                ],
                'suggest'                 => [
                    'type'            => 'completion',
                    'analyzer'        => 'search_analyzer',
                    'search_analyzer' => 'search_analyzer',
                ],
            ]);

            $itemMapping->send();
        }  catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }
    }

    /**
     * Add documents
     *
     * @param RepositoryReference $repositoryReference
     * @param Document[] $documents
     *
     * @throws ResourceExistsException
     */
    public function addDocuments(
        RepositoryReference $repositoryReference,
        array $documents
    )
    {
        try {
            $this
                ->getType($repositoryReference, self::ITEM_TYPE)
                ->addDocuments($documents);

        }  catch (BulkResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }
    }

    /**
     * Delete documents by its
     *
     * @param RepositoryReference $repositoryReference
     * @param string[] $documentsId
     *
     * @throws ResourceExistsException
     */
    public function deleteDocumentsByIds(
        RepositoryReference $repositoryReference,
        array $documentsId
    )
    {
        try {
            $this
                ->getType($repositoryReference, self::ITEM_TYPE)
                ->deleteIds($documentsId);

        }  catch (BulkResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::indexNotAvailable();
        }
    }
}
