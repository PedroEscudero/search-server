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

use Apisearch\Event\Event;
use Apisearch\Event\EventRepository as BaseEventRepository;
use Apisearch\Event\Stats;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\RepositoryWithCredentials;
use DateTime;
use Elastica\Aggregation\Terms;
use Elastica\Client;
use Elastica\Document as ElasticaDocument;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query as ElasticaQuery;
use Elastica\Result;
use Elastica\Type;
use Elastica\Type\Mapping;

/**
 * Class EventRepository.
 */
class EventRepository extends RepositoryWithCredentials implements BaseEventRepository
{
    /**
     * @var string
     *
     * Item type
     */
    const EVENT_TYPE = 'event';

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
     * Create index.
     *
     * @param int  $shards
     * @param int  $replicas
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        int $shards,
        int $replicas
    ) {
        try {
            $this
                ->getEventsIndex()
                ->create([
                    'number_of_shards' => $shards,
                    'number_of_replicas' => $replicas,
                ]);
            $this->createEventIndexMapping();

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            var_dump($this->getRepositoryReference()->compose());
            throw ResourceExistsException::eventsIndexExists();
        }
    }

    /**
     * Delete index.
     *
     * @throws ResourceNotAvailableException
     */
    public function deleteIndex()
    {
        try {
            $this
                ->getEventsIndex()
                ->delete();

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }
    }

    /**
     * Save event.
     *
     * @param Event $event
     *
     * @throws ResourceNotAvailableException
     */
    public function save(Event $event)
    {
        $formattedTime = $this->formatTimeFromMillisecondsToBasicDateTime($event->getOccurredOn());
        $itemDocument = [
            'name' => $event->getName(),
            'payload' => $event->getPayload(),
            'occurred_on' => $formattedTime,
        ];

        $elasticaDocument = new ElasticaDocument(
            $event->getConsistencyHash(),
            $itemDocument
        );

        try {
            $this
                ->getType(self::EVENT_TYPE)
                ->addDocument($elasticaDocument);

            $this->refresh();

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }
    }

    /**
     * Get all events.
     *
     * @param string|null $name
     * @param int|null    $from
     * @param int|null    $to
     * @param int|null    $length
     * @param int|null    $offset
     *
     * @return Event[]
     *
     * @throws ResourceNotAvailableException
     */
    public function all(
        string $name = null,
        ? int $from = null,
        ? int $to = null,
        ? int $length = 10,
        ? int $offset = 0
    ): array {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();

        if (!is_null($name)) {
            $boolQuery->addMust(new ElasticaQuery\Term(['name' => $name]));
        }

        $range = [];

        if (!is_null($from)) {
            $range['gte'] = $from;
        }

        if (!is_null($to)) {
            $range['lt'] = $to;
        }

        if (!empty($range)) {
            $boolQuery->addMust(new ElasticaQuery\Range('occurred_on', $range));
        }

        $mainQuery->setQuery($boolQuery);

        try {
            $queryResult = $this
                ->getEventsIndex()
                ->search($mainQuery, [
                    'from' => $offset,
                    'size' => $length,
                ]);

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }

        return $this->resultsToEvents($queryResult->getResults());
    }

    /**
     * Get last event.
     *
     * @return Event|null
     *
     * @throws ResourceNotAvailableException
     */
    public function last(): ? Event
    {
        $mainQuery = new ElasticaQuery();

        try {
            $queryResult = $this
                ->getEventsIndex()
                ->search($mainQuery, [
                    'from' => 0,
                    'size' => 1,
                ]);

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }

        $results = $queryResult->getResults();
        if (empty($results)) {
            return null;
        }

        $firstResult = reset($results);

        return $this->resultToEvent($firstResult);
    }

    /**
     * Get stats.
     *
     * @param int|null $from
     * @param int|null $to
     *
     * @return Stats
     *
     * @throws ResourceNotAvailableException
     */
    public function stats(
        ? int $from = null,
        ? int $to = null
    ): Stats {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();
        $range = [];

        if (!is_null($from)) {
            $range['gte'] = $this->formatTimeFromMillisecondsToBasicDateTime($from);
        }

        if (!is_null($to)) {
            $range['lt'] = $this->formatTimeFromMillisecondsToBasicDateTime($to);
        }

        if (!empty($range)) {
            $boolQuery->addMust(new ElasticaQuery\Range('occurred_on', $range));
        }

        $mainQuery->setQuery($boolQuery);
        $aggregation = new Terms('name');
        $aggregation->setField('name');
        $mainQuery->addAggregation($aggregation);

        try {
            $queryResult = $this
                ->getEventsIndex()
                ->search($mainQuery, [
                    'from' => 0,
                    'size' => 0,
                ]);

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }

        $nameAggregationResults = $queryResult->getAggregation('name');
        $names = [
            'IndexWasReset' => 0,
            'ItemsWereDeleted' => 0,
            'ItemsWereIndexed' => 0,
            'QueryWasMade' => 0,
        ];

        array_walk($nameAggregationResults['buckets'], function (array $item) use (&$names) {
            $names[$item['key']] = $item['doc_count'];
        });

        return Stats::createByPlainData(
            array_filter($names, function (int $elements) {
                return $elements > 0;
            })
        );
    }

    /**
     * Create event index mapping.
     */
    private function createEventIndexMapping()
    {
        try {
            $itemMapping = new Mapping();
            $itemMapping->setType($this->getType(self::EVENT_TYPE));
            $itemMapping->setProperties([
                'name' => [
                    'type' => 'keyword',
                ],
                'payload' => [
                    'type' => 'text',
                    'index' => false,
                ],
                'occurred_on' => [
                    'type' => 'date',
                    'format' => 'basic_date_time',
                ],
            ]);

            $itemMapping->send();

        } catch (ResponseException $exception) {
            /**
             * The index resource cannot be deleted.
             * This means that the resource is not available
             */
            throw ResourceNotAvailableException::eventsIndexNotAvailable();
        }
    }

    /**
     * Get events index.
     *
     * @return Index
     */
    private function getEventsIndex(): Index
    {
        return $this
            ->client
            ->getIndex("apisearch_{$this->getRepositoryReference()->compose()}_events");
    }

    /**
     * Create index.
     *
     * @param string $typeName
     *
     * @return Type
     */
    private function getType(string $typeName)
    {
        return $this
            ->getEventsIndex()
            ->getType($typeName);
    }

    /**
     * Refresh.
     */
    private function refresh()
    {
        $this
            ->getEventsIndex()
            ->refresh();
    }

    /**
     * Result to Event.
     *
     * @param Result $result
     *
     * @return Event
     */
    private function resultToEvent(Result $result): Event
    {
        $occurredOn = DateTime::createFromFormat('Ymd\THis.uP', $result->occurred_on);

        return Event::createByPlainData(
            (string) $result->getId(),
            (string) $result->name,
            (string) $result->payload,
            (int) $occurredOn->format('Uu')
        );
    }

    /**
     * Results to Events.
     *
     * @param Result[] $results
     *
     * @return Event[]
     */
    private function resultsToEvents(array $results): array
    {
        return array_map(function (Result $result) {
            return $this->resultToEvent($result);
        }, $results);
    }

    /**
     * Format date from epoch_time with microseconds to elasticsearch
     * basic_date_time.
     *
     * @param int $time
     *
     * @return string
     */
    private function formatTimeFromMillisecondsToBasicDateTime(int $time): string
    {
        $formattedDatetime = (string) ($time / 1000000);
        if (10 === strlen($formattedDatetime)) {
            $formattedDatetime .= '.';
        }

        $formattedDatetime = str_pad($formattedDatetime, 17, '0', STR_PAD_RIGHT);
        $datetime = DateTime::createFromFormat('U.u', $formattedDatetime);

        return
            $datetime->format('Ymd\THis').'.'.
            str_pad(((string) (int) (((int) $datetime->format('u')) / 1000)), 3, '0', STR_PAD_LEFT).
            $datetime->format('P');
    }
}
