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

use Apisearch\Repository\RepositoryReference;
use Elastica\Client;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;
use Exception;

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
            ->getIndex('apisearch_'.$repositoryReference->compose());
    }

    /**
     * Delete index.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function deleteIndex(RepositoryReference $repositoryReference)
    {
        try {
            $this->getSearchIndex($repositoryReference)->delete();
        } catch (Exception $e) {
            // Silent pass
        }
    }

    /**
     * Create index.
     *
     * @param RepositoryReference $repositoryReference
     * @param int                 $shards
     * @param int                 $replicas
     * @param null|string         $language
     */
    public function createIndex(
        RepositoryReference $repositoryReference,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->deleteIndex($repositoryReference);
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

        $searchIndex->create($indexConfiguration, true);
        $searchIndex->clearCache();
        $searchIndex->refresh();
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
     * @return mixed
     */
    public function search(
        RepositoryReference $repositoryReference,
        Query $query,
        int $from,
        int $size
    ) {
        $queryResult = $this
            ->getSearchIndex($repositoryReference)
            ->search($query, [
                'from' => $from,
                'size' => $size,
            ]);

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
     * @param int                 $shards
     * @param int                 $replicas
     * @param null|string         $language
     */
    public function createIndexMapping(
        RepositoryReference $repositoryReference,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->createIndex($repositoryReference, $shards, $replicas, $language);
        $this->createItemIndexMapping($repositoryReference);
        $this->refresh($repositoryReference);
    }

    /**
     * Create item index mapping.
     *
     * @param RepositoryReference $repositoryReference
     */
    private function createItemIndexMapping(RepositoryReference $repositoryReference)
    {
        $itemMapping = new Mapping();
        $itemMapping->setType($this->getType($repositoryReference, self::ITEM_TYPE));
        $itemMapping->setParam('dynamic_templates', [
            [
                'dynamic_metadata_as_keywords' => [
                    'path_match' => 'indexed_metadata.*',
                    'match_mapping_type' => 'string',
                    'mapping' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            [
                'dynamic_searchable_metadata_as_text' => [
                    'path_match' => 'searchable_metadata.*',
                    'mapping' => [
                        'type' => 'text',
                        'analyzer' => 'default',
                        'search_analyzer' => 'search_analyzer',
                    ],
                ],
            ],
        ]);
        $itemMapping->setProperties([
            'uuid' => [
                'type' => 'object',
                'dynamic' => 'strict',
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'type' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'coordinate' => ['type' => 'geo_point'],
            'metadata' => [
                'type' => 'object',
                'dynamic' => true,
                'enabled' => false,
            ],
            'indexed_metadata' => [
                'type' => 'object',
                'dynamic' => true,
            ],
            'searchable_metadata' => [
                'type' => 'object',
                'dynamic' => true,
            ],
            'exact_matching_metadata' => [
                'type' => 'keyword',
                'normalizer' => 'exact_matching_normalizer',
            ],
            'suggest' => [
                'type' => 'completion',
                'analyzer' => 'search_analyzer',
                'search_analyzer' => 'search_analyzer',
            ],
        ]);

        $itemMapping->send();
    }
}
