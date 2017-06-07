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

namespace Puntmig\Search\Server\Core;

use Elastica\Document;
use Elastica\Document as ElasticaDocument;

use Puntmig\Search\Model\Coordinate;
use Puntmig\Search\Model\Item;
use Puntmig\Search\Server\Elastica\ElasticaWrapper;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWithKeyWrapper
{
    /**
     * Create the index.
     */
    public function createIndex()
    {
        $this
            ->elasticaWrapper
            ->createIndexMapping(
                $this->key,
                4
            );
    }

    /**
     * Generate items documents.
     *
     * @param Item[] $items
     */
    public function addItems(array $items)
    {
        $documents = [];
        foreach ($items as $item) {
            $documents[] = $this->createItemDocument($item);
        }

        if (empty($documents)) {
            return;
        }

        $this
            ->elasticaWrapper
            ->getType($this->key, ElasticaWrapper::ITEM_TYPE)
            ->addDocuments($documents);

        $this->refresh();
    }

    /**
     * Create item document.
     *
     * @param Item $item
     *
     * @return Document
     */
    private function createItemDocument(Item $item) : Document
    {
        $uuid = $item->getUUID();
        $itemDocument = [
            'uuid' => [
                'id' => $uuid->getId(),
                'type' => $uuid->getType(),
            ],
            'coordinate' => $item->getCoordinate() instanceof Coordinate
                ? $item
                    ->getCoordinate()
                    ->toArray()
                : null,
            'metadata' => array_filter($item->getMetadata()),
            'indexed_metadata' => array_filter($item->getIndexedMetadata()),
            'searchable_metadata' => array_filter($item->getSearchableMetadata()),
            'exact_matching_metadata' => array_filter($item->getExactMatchingMetadata()),
            'suggest' => array_filter($item->getSuggest()),
        ];

        return new ElasticaDocument($uuid->composeUUID(), $itemDocument);
    }
}
