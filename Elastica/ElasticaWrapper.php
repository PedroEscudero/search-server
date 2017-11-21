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

namespace Puntmig\Search\Server\Elastica;

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
     * @param string $appId
     *
     * @return Index
     */
    public function getSearchIndex(string $appId): Index
    {
        return $this
            ->client
            ->getIndex("puntmig_$appId");
    }

    /**
     * Delete index.
     *
     * @param string $appId
     */
    public function deleteIndex(string $appId)
    {
        try {
            $this->getSearchIndex($appId)->delete();
        } catch (Exception $e) {
            // Silent pass
        }
    }

    /**
     * Create index.
     *
     * @param string      $appId
     * @param int         $shards
     * @param int         $replicas
     * @param null|string $language
     */
    public function createIndex(
        string $appId,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->deleteIndex($appId);
        $searchIndex = $this->getSearchIndex($appId);
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
     * @param string $appId
     * @param string $typeName
     *
     * @return Type
     */
    public function getType(
        string $appId,
        string $typeName
    ) {
        return $this
            ->getSearchIndex($appId)
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param string $appId
     * @param Query  $query
     * @param int    $from
     * @param int    $size
     *
     * @return mixed
     */
    public function search(
        string $appId,
        Query $query,
        int $from,
        int $size
    ) {
        $queryResult = $this
            ->getSearchIndex($appId)
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
     * @param string $appId
     */
    public function refresh(string $appId)
    {
        $this
            ->getSearchIndex($appId)
            ->refresh();
    }

    /**
     * Create mapping.
     *
     * @param string      $appId
     * @param int         $shards
     * @param int         $replicas
     * @param null|string $language
     */
    public function createIndexMapping(
        string $appId,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->createIndex($appId, $shards, $replicas, $language);
        $this->createItemIndexMapping($appId);
        $this->refresh($appId);
    }

    /**
     * Create item index mapping.
     *
     * @param string $appId
     */
    private function createItemIndexMapping(string $appId)
    {
        $itemMapping = new Mapping();
        $itemMapping->setType($this->getType($appId, 'item'));
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
