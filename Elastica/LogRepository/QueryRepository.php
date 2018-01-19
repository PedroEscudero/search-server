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

use Apisearch\Log\Log;
use Apisearch\Query\Query;
use Apisearch\Result\Logs;
use Apisearch\Server\Elastica\Builder\QueryBuilder;
use Apisearch\Server\Elastica\Builder\ResultBuilder;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use DateTime;
use Elastica\Query as ElasticaQuery;

/**
 * Class QueryRepository.
 */
class QueryRepository extends ElasticaWrapperWithRepositoryReference
{
    /**
     * @var QueryBuilder
     *
     * Query builder
     */
    private $queryBuilder;

    /**
     * @var ResultBuilder
     *
     * Result builder
     */
    private $resultBuilder;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper $elasticaWrapper
     * @param array           $repositoryConfig
     * @param QueryBuilder    $queryBuilder
     * @param ResultBuilder   $resultBuilder
     */
    public function __construct(
        ElasticaWrapper $elasticaWrapper,
        array $repositoryConfig,
        QueryBuilder $queryBuilder,
        ResultBuilder $resultBuilder
    ) {
        parent::__construct(
            $elasticaWrapper,
            $repositoryConfig
        );

        $this->queryBuilder = $queryBuilder;
        $this->resultBuilder = $resultBuilder;
    }

    /**
     * Make a query and return an Logs instance.
     *
     * @param Query    $query
     * @param int|null $from
     * @param int|null $to
     *
     * @return Logs
     */
    public function query(
        Query $query,
        ?int $from,
        ?int $to
    ): Logs {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();
        $this
            ->queryBuilder
            ->buildQuery(
                $query,
                $mainQuery,
                $boolQuery
            );

        $range = [];

        if (!empty($from)) {
            $range['gte'] = $from;
        }

        if (!empty($to)) {
            $range['lt'] = $to;
        }

        if (!empty($range)) {
            $boolQuery->addMust(new ElasticaQuery\Range('indexed_metadata.occurred_on', $range));
        }

        $results = $this
            ->elasticaWrapper
            ->search(
                $this->getRepositoryReference(),
                $mainQuery,
                $query->areResultsEnabled()
                    ? $query->getFrom()
                    : 0,
                $query->areResultsEnabled()
                    ? $query->getSize()
                    : 0
            );

        return $this->elasticaResultToLogs(
            $query,
            $results
        );
    }

    /**
     * Build an Logs object given elastica result object.
     *
     * @param Query $query
     * @param array $elasticaResults
     *
     * @return Logs
     */
    private function elasticaResultToLogs(
        Query $query,
        array $elasticaResults
    ): Logs {
        $resultAggregations = [];

        /*
         * Aggregations extraction
         */
        if ($query->areAggregationsEnabled()) {
            $resultAggregations = $elasticaResults['aggregations']['all']['universe'];
            unset($resultAggregations['common']);
        }

        $logs = new Logs(
            $query,
            $elasticaResults['total_hits']
        );

        /*
         * @var ElasticaResult
         */
        foreach ($elasticaResults['results'] as $elasticaResult) {
            $logs->addLog($this->elasticResultToLog(
                $elasticaResult->getData()
            ));
        }

        if (
            $query->areAggregationsEnabled() &&
            isset($resultAggregations['doc_count'])
        ) {
            $logs->setAggregations(
                $this
                    ->resultBuilder
                    ->buildResultAggregations(
                        $query,
                        $resultAggregations
                    )
            );
        }

        return $logs;
    }

    /**
     * Create a Log from an elastic result.
     *
     * @param array $result
     *
     * @return Log
     */
    private function elasticResultToLog(array $result): Log
    {
        $indexedMetadata = $result['indexed_metadata'];
        $occurredOn = DateTime::createFromFormat(
            'Ymd\THis.uP',
            $indexedMetadata['occurred_on']
        );
        unset($indexedMetadata['occurred_on']);

        return Log::createFromArray([
            'id ' => (string) $result['uuid']['id'],
            'type' => (string) $result['uuid']['type'],
            'payload' => (string) $result['payload'],
            'occurred_on' => (int) $occurredOn->format('Uu'),
        ]);
    }
}
