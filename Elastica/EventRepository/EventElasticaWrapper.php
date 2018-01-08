<?php
/**
 * File header placeholder
 */

namespace Apisearch\Server\Elastica\EventRepository;

use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Elastica\Type\Mapping;

/**
 * Class EventElasticaWrapper
 */
class EventElasticaWrapper extends ElasticaWrapper
{
    /**
     * @var string
     *
     * Item type
     */
    const ITEM_TYPE = 'event';

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
        return 'apisearch_' . $repositoryReference->compose() . '_events';
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
        return ResourceNotAvailableException::eventsIndexNotAvailable($message);
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
            'indexed_metadata' => [
                'type' => 'object',
                'dynamic' => true,
                'properties' => [
                    'occurred_on' => [
                        'type' => 'date',
                        'format' => 'basic_date_time',
                    ],
                ],
            ],
            'payload' => [
                'type' => 'text',
                'index' => false,
            ],
        ]);
    }
}