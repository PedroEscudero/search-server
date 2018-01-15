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

namespace Apisearch\Server\Elastica\LogRepository;

use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Elastica\Type\Mapping;

/**
 * Class LogElasticaWrapper.
 */
class LogElasticaWrapper extends ElasticaWrapper
{
    /**
     * @var string
     *
     * Item type
     */
    const ITEM_TYPE = 'log_entry';

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
        return 'apisearch_'.$repositoryReference->compose().'_logs';
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
        return ResourceNotAvailableException::logsIndexNotAvailable($message);
    }

    /**
     * Get index configuration.
     *
     * @param int $shards
     * @param int $replicas
     *
     * @return array
     */
    public function getIndexConfiguration(
        int $shards,
        int $replicas
    ): array {
        return [
            'number_of_shards' => $shards,
            'number_of_replicas' => $replicas,
        ];
    }

    /**
     * Build index mapping.
     *
     * @param Mapping $mapping
     */
    public function buildIndexMapping(Mapping $mapping)
    {
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
                'dynamic' => false,
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
