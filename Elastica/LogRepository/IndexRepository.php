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

use Apisearch\Config\ImmutableConfig;
use Apisearch\Log\Log;
use Apisearch\Server\Domain\Repository\LogRepository\IndexRepository as IndexRepositoryInterface;
use Apisearch\Server\Elastica\Builder\TimeFormatBuilder;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Elastica\Document;
use Elastica\Document as ElasticaDocument;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWrapperWithRepositoryReference implements IndexRepositoryInterface
{
    /**
     * @var TimeFormatBuilder
     *
     * Time format builder
     */
    private $timeFormatBuilder;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper   $elasticaWrapper
     * @param array             $repositoryConfig
     * @param TimeFormatBuilder $timeFormatBuilder
     */
    public function __construct(
        ElasticaWrapper $elasticaWrapper,
        array $repositoryConfig,
        TimeFormatBuilder $timeFormatBuilder
    ) {
        parent::__construct(
            $elasticaWrapper,
            $repositoryConfig
        );

        $this->timeFormatBuilder = $timeFormatBuilder;
    }

    /**
     * Create the index.
     */
    public function createIndex()
    {
        $this
            ->elasticaWrapper
            ->createIndex(
                $this->getRepositoryReference(),
                ImmutableConfig::createEmpty(),
                $this->repositoryConfig['shards'],
                $this->repositoryConfig['replicas']
            );

        $this
            ->elasticaWrapper
            ->createIndexMapping(
                $this->getRepositoryReference(),
                ImmutableConfig::createEmpty()
            );

        $this->refresh();
    }

    /**
     * Delete the index.
     */
    public function deleteIndex()
    {
        $this
            ->elasticaWrapper
            ->deleteIndex($this->getRepositoryReference());
    }

    /**
     * Generate log document.
     *
     * @param Log $log
     */
    public function addLog(Log $log)
    {
        $this
            ->elasticaWrapper
            ->addDocuments(
                $this->getRepositoryReference(),
                [$this->createLogDocument($log)]
            );

        $this->refresh();
    }

    /**
     * Create item document.
     *
     * @param Log $log
     *
     * @return Document
     */
    private function createLogDocument(Log $log): Document
    {
        $formattedTime = $this
            ->timeFormatBuilder
            ->formatTimeFromMillisecondsToBasicDateTime(
                $log->getOccurredOn()
            );

        $itemDocument = [
            'uuid' => [
                'id' => $log->getId(),
                'type' => $log->getType(),
            ],
            'payload' => $log->getPayload(),
            'indexed_metadata' => [
                'occurred_on' => $formattedTime,
            ],
        ];

        return new ElasticaDocument(
            $log->getId(),
            $itemDocument
        );
    }
}
