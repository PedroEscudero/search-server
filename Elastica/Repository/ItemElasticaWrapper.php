<?php
/**
 * File header placeholder
 */

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Elastica\ElasticaLanguages;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;

/**
 * Class ItemElasticaWrapper
 */
class ItemElasticaWrapper extends ElasticaWrapper
{
    /**
     * @var string
     *
     * Item type
     */
    const ITEM_TYPE = 'item';

    /**
     * Get item type
     *
     * @return string
     */
    public function getItemType() : string
    {
        return self::ITEM_TYPE;
    }

    /**
     * Get index name
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return string
     */
    public function getIndexName(RepositoryReference $repositoryReference): string
    {
        return 'apisearch_' . $repositoryReference->compose();
    }


    /**
     * Get index not available exception
     *
     * @param string $message
     *
     * @return ResourceNotAvailableException
     */
    public function getIndexNotAvailableException(string $message) : ResourceNotAvailableException
    {
        return ResourceNotAvailableException::indexNotAvailable($message);
    }

    /**
     * Get index configuration
     *
     * @param int $shards
     * @param int $replicas
     *
     * @return array
     */
    public function getIndexConfiguration(
        int $shards,
        int $replicas
    ) : array
    {
        return [
            'number_of_shards' => $shards,
            'number_of_replicas' => $replicas,
            'max_result_window' => 50000,
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'asciifolding',
                            'ngram_filter',
                        ],
                    ],
                    'search_analyzer' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'asciifolding',
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
    }

    /**
     * Build index mapping
     *
     * @param Mapping $mapping
     */
    public function buildIndexMapping(Mapping $mapping)
    {
        $mapping->setParam('dynamic_templates', [
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

        $mapping->setProperties([
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
    }

    /**
     * Update index configuration.
     *
     * @param RepositoryReference $repositoryReference
     * @param string              $configPath
     * @param null|string         $language
     */
    public function updateIndexSettings(
        RepositoryReference $repositoryReference,
        string $configPath,
        ? string $language
    ) {
        $searchIndex = $this->getIndex($repositoryReference);
        $indexSettings = [
            'analysis' => [
                'analyzer' => [
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
                    'stop_words' => [
                        'type' => 'stop',
                        'stopwords' => ElasticaLanguages::getStopwordsLanguageByIso($language),
                    ],
                ],
            ],
        ];

        $synonymPath = $configPath.'/synonyms.txt';
        if (file_exists($synonymPath)) {
            $indexSettings['analysis']['analyzer']['search_analyzer']['filter'][] = 'synonym';
            $indexSettings['analysis']['filter']['synonym'] = [
                'type' => 'synonym',
                'synonyms_path' => $synonymPath,
            ];
        }

        $stemmer = ElasticaLanguages::getStemmerLanguageByIso($language);
        if (!is_null($stemmer)) {
            $indexSettings['analysis']['analyzer']['search_analyzer']['filter'][] = 'stemmer';
            $indexSettings['analysis']['filter']['stemmer'] = [
                'type' => 'stemmer',
                'name' => $stemmer,
            ];
        }

        try {
            $searchIndex->close();
            $searchIndex->setSettings($indexSettings);
            $searchIndex->open();
            sleep(1);
        } catch (ResponseException $exception) {
            /*
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceExistsException::indexExists();
        }
    }
}