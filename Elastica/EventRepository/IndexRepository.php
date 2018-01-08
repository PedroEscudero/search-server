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

namespace Apisearch\Server\Elastica\EventRepository;

use Apisearch\Event\Event;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;
use Elastica\Document;
use Elastica\Document as ElasticaDocument;
use DateTime;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWrapperWithRepositoryReference
{
    /**
     * Create the index.
     */
    public function createIndex()
    {
        $this
            ->elasticaWrapper
            ->createIndex(
                $this->getRepositoryReference(),
                $this->repositoryConfig['shards'],
                $this->repositoryConfig['replicas']
            );

        $this
            ->elasticaWrapper
            ->createIndexMapping($this->getRepositoryReference());

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
     * Generate event document.
     *
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        $this
            ->elasticaWrapper
            ->addDocuments(
                $this->getRepositoryReference(),
                [$this->createEventDocument($event)]
            );

        $this->refresh();
    }

    /**
     * Create item document.
     *
     * @param Event $event
     *
     * @return Document
     */
    private function createEventDocument(Event $event): Document
    {
        $formattedTime = $this->formatTimeFromMillisecondsToBasicDateTime($event->getOccurredOn());
        $itemDocument = [
            'uuid' => [
                'id' => $event->getConsistencyHash(),
                'type' => $event->getName()
            ],
            'payload' => $event->getPayload(),
            'indexed_metadata' => [
                'occurred_on' => $formattedTime
            ] + $event->getIndexablePayload(),
        ];

        return new ElasticaDocument(
            $event->getConsistencyHash(),
            $itemDocument
        );
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
