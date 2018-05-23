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
use Apisearch\Config\ImmutableConfig;
use Apisearch\Config\Synonym;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Elastica\ElasticaLanguages;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Exception\ParsedResourceNotAvailableException;
use Elastica\Exception\ResponseException;
use Elastica\Type\Mapping;

/**
 * Class ItemElasticaWrapper.
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
     * Get item type.
     *
     * @return string
     */
    public function getItemType(): string
    {
        return self::ITEM_TYPE;
    }

    /**
     * Get index name.
     *
     * @param RepositoryReference $repositoryReference
     *
     * @return string
     */
    public function getIndexName(RepositoryReference $repositoryReference): string
    {
        return $this->buildIndexReference(
            $repositoryReference,
            'apisearch_item'
        );
    }

    /**
     * Get index not available exception.
     *
     * @param string $message
     *
     * @return ResourceNotAvailableException
     */
    public function getIndexNotAvailableException(string $message): ResourceNotAvailableException
    {
        return ParsedResourceNotAvailableException::parsedIndexNotAvailable($message);
    }

    /**
     * Get index configuration.
     *
     * @param ImmutableConfig $config
     * @param int             $shards
     * @param int             $replicas
     *
     * @return array
     */
    public function getIndexConfiguration(
        ImmutableConfig $config,
        int $shards,
        int $replicas
    ): array {
        $language = $config->getLanguage();
        $indexConfiguration = [
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

        $stopWordsLanguage = ElasticaLanguages::getStopwordsLanguageByIso($language);
        if (!is_null($stopWordsLanguage)) {
            $indexConfiguration['analysis']['analyzer']['search_analyzer']['filter'][] = 'stop_words';
            $indexConfiguration['analysis']['filter']['stop_words'] = [
                'type' => 'stop',
                'stopwords' => $stopWordsLanguage,
            ];
        }

        $stemmer = ElasticaLanguages::getStemmerLanguageByIso($language);
        if (!is_null($stemmer)) {
            $indexConfiguration['analysis']['analyzer']['search_analyzer']['filter'][] = 'stemmer';
            $indexConfiguration['analysis']['filter']['stemmer'] = [
                'type' => 'stemmer',
                'name' => $stemmer,
            ];
        }

        $synonyms = $config->getSynonyms();
        if (!empty($synonyms)) {
            $indexConfiguration['analysis']['analyzer']['search_analyzer']['filter'][] = 'synonym';
            $indexConfiguration['analysis']['filter']['synonym'] = [
                'type' => 'synonym',
                'synonyms' => array_map(function (Synonym $synonym) {
                    return $synonym->expand();
                }, $synonyms),
            ];
        }

        return $indexConfiguration;
    }

    /**
     * Build index mapping.
     *
     * @param Mapping         $mapping
     * @param ImmutableConfig $config
     */
    public function buildIndexMapping(
        Mapping $mapping,
        ImmutableConfig $config
    ) {
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

        $sourceExcludes = ['exact_matching_metadata'];
        if (!$config->shouldSearchableMetadataBeStored()) {
            $sourceExcludes[] = 'searchable_metadata';
        }

        $mapping->setSource(['excludes' => $sourceExcludes]);

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
     * @param Config              $config
     */
    public function updateIndexSettings(
        RepositoryReference $repositoryReference,
        string $configPath,
        Config $config
    ) {
        return;

        /*
         * Nothing to do ATM
         *
         * If Index settings change in this method, you should uncomment next
         * code in order to update these settings while the index is closed
         */

        /*
        $searchIndex = $this->getIndex($repositoryReference);

        try {
            $searchIndex->close();
            $searchIndex->setSettings($indexSettings);
            $searchIndex->open();
            sleep(1);
        } catch (ResponseException $exception) {

            throw ResourceExistsException::indexExists();
        }
        */
    }
}
