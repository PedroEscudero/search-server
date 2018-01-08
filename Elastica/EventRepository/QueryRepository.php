<?php
/**
 * File header placeholder
 */

namespace Apisearch\Server\Elastica\EventRepository;

use Apisearch\Event\Event;
use Apisearch\Query\Query;
use Apisearch\Result\Events;
use Apisearch\Server\Elastica\Builder\QueryBuilder;
use Apisearch\Server\Elastica\Builder\ResultBuilder;
use Apisearch\Server\Elastica\ElasticaWrapper;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Elastica\Aggregation as ElasticaAggregation;
use Elastica\Query as ElasticaQuery;
use Elastica\Result as ElasticaResult;
use DateTime;

/**
 * Class QueryRepository
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
     * @param QueryBuilder $queryBuilder
     * @param ResultBuilder $resultBuilder
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
     * Make a query and return an Events instance
     *
     * @param Query $query
     * @param int|null            $from
     * @param int|null            $to
     *
     * @return Events
     */
    public function query(
        Query $query,
        ?int $from,
        ?int $to
    ): Events
    {
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

        return $this->elasticaResultToEvents(
            $query,
            $results
        );
    }

    /**
     * Build an Events object given elastica result object.
     *
     * @param Query $query
     * @param array $elasticaResults
     *
     * @return Events
     */
    private function elasticaResultToEvents(
        Query $query,
        array $elasticaResults
    ): Events {

        $resultAggregations = [];

        /**
         * Aggregations extraction
         */
        if ($query->areAggregationsEnabled()) {
            $resultAggregations = $elasticaResults['aggregations']['all']['universe'];
            unset($resultAggregations['common']);
        }

        $events = new Events(
            $query,
            $elasticaResults['total_hits']
        );

        /**
         * @var ElasticaResult $elasticaResult
         */
        foreach ($elasticaResults['results'] as $elasticaResult) {
            $events->addEvent($this->elasticResultToEvent(
                $elasticaResult->getData()
            ));
        }

        if (
            $query->areAggregationsEnabled() &&
            isset($resultAggregations['doc_count'])
        ) {
            $events->setAggregations(
                $this
                    ->resultBuilder
                    ->buildResultAggregations(
                        $query,
                        $resultAggregations
                    )
            );
        }

        return $events;
    }

    /**
     * Create an Event from an elastic result
     *
     * @param array $result
     *
     * @return Event
     */
    private function elasticResultToEvent(array $result) : Event
    {
        $indexedMetadata = $result['indexed_metadata'];
        $occurredOn = DateTime::createFromFormat(
            'Ymd\THis.uP',
            $indexedMetadata['occurred_on']
        );
        unset($indexedMetadata['occurred_on']);

        return Event::createByPlainData(
            (string) $result['uuid']['id'],
            (string) $result['uuid']['type'],
            (string) $result['payload'],
            (array) $indexedMetadata,
            (int) $occurredOn->format('Uu')
        );
    }
}