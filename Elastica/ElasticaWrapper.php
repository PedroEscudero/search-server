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
     * @param string $key
     *
     * @return Index
     */
    public function getSearchIndex(string $key) : Index
    {
        return $this
            ->client
            ->getIndex("puntmig_$key");
    }

    /**
     * Delete index.
     */
    public function deleteIndex(string $key)
    {
        try {
            $this->getSearchIndex($key)->delete();
        } catch (Exception $e) {

            // Silent pass
        }
    }

    /**
     * Create index.
     *
     * @param string      $key
     * @param int         $shards
     * @param int         $replicas
     * @param null|string $language
     */
    public function createIndex(
        string $key,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->deleteIndex($key);
        $searchIndex = $this->getSearchIndex($key);
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
                            'ngram_filter',
                            'stop_words',
                        ],
                    ],
                    'search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'stop_words',
                        ],
                    ],
                ],
                'filter' => [
                    'ngram_filter' => [
                        'type' => 'edge_ngram',
                        'min_gram' => 2,
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
     * @param string $key
     * @param string $typeName
     *
     * @return Type
     */
    public function getType(
        string $key,
        string $typeName
    ) {
        return $this
            ->getSearchIndex($key)
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param string $key
     * @param Query  $query
     * @param int    $from
     * @param int    $size
     *
     * @return mixed
     */
    public function search(
        string $key,
        Query $query,
        int $from,
        int $size
    ) {
        $queryResult = $this
            ->getSearchIndex($key)
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
     * @param string $key
     */
    public function refresh(string $key)
    {
        $this
            ->getSearchIndex($key)
            ->refresh();
    }

    /**
     * Create mapping.
     *
     * @param string      $key
     * @param int         $shards
     * @param int         $replicas
     * @param null|string $language
     */
    public function createIndexMapping(
        string $key,
        int $shards,
        int $replicas,
        ? string $language
    ) {
        $this->createIndex($key, $shards, $replicas, $language);
        $this->createItemIndexMapping($key);
        $this->refresh($key);
    }

    /**
     * Create item index mapping.
     *
     * @param string $key
     */
    private function createItemIndexMapping(string $key)
    {
        $itemMapping = new Mapping();
        $itemMapping->setType($this->getType($key, 'item'));
        $itemMapping->enableAllField(false);
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
        ]);
        $itemMapping->setProperties([
            'uuid' => [
                'type' => 'object',
                'dynamic' => 'strict',
                'include_in_all' => false,
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
                'include_in_all' => false,
            ],
            'searchable_metadata' => [
                'type' => 'text',
                'include_in_all' => false,
                'analyzer' => 'default',
                'search_analyzer' => 'search_analyzer',
            ],
            'exact_matching_metadata' => [
                'type' => 'text',
                'include_in_all' => false,
                'analyzer' => 'standard',
                'search_analyzer' => 'standard',
            ],
            'suggest' => [
                'type' => 'completion',
            ],
        ]);

        $itemMapping->send();
    }
}
