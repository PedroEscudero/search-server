<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Elastica;

use Elastica\Client;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;

use Mmoreram\SearchBundle\Model\Model;

/**
 * Class ElasticaWrapper.
 */
class ElasticaWrapper
{
    /**
     * @var Client
     *
     * Elastica client
     */
    private $client;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->client = new Client([
            'servers' => [
                'default' => [
                    'host' => 'localhost',
                    'port' => 9200,
                ],
            ],
        ]);
    }

    /**
     * Get search index.
     *
     * @return Index
     */
    public function getSearchIndex() : Index
    {
        return $this
            ->client
            ->getIndex('search');
    }

    /**
     * Create index.
     */
    public function createIndex()
    {
        $tagIndex = $this->getSearchIndex();
        $tagIndex->create([
            'number_of_shards' => 1,
            'number_of_replicas' => 1,
            'analysis' => [
                'analyzer' => [
                    'default' => [
                        'type' => 'custom',
                        'tokenizer' => 'standard',
                        'filter' => [
                            'lowercase',
                            'ngram_filter',
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
            ],
        ], true);
        $tagIndex->clearCache();
        $tagIndex->refresh();
    }

    /**
     * Create index.
     *
     * @param string $typeName
     *
     * @return Type
     */
    public function getType(string $typeName)
    {
        return $this
            ->getSearchIndex()
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param Query $query
     * @param int   $from
     * @param int   $size
     *
     * @return mixed
     */
    public function search(
        Query $query,
        int $from,
        int $size
    ) {
        $queryResult = $this
            ->getSearchIndex()
            ->search($query, [
                'from' => $from,
                'size' => $size,
            ]);

        return [
            'results' => $queryResult->getResults(),
            'aggregations' => $queryResult->getAggregations(),
        ];
    }

    /**
     * Refresh.
     */
    public function refresh()
    {
        $this
            ->getSearchIndex()
            ->refresh();
    }

    /**
     * Create mapping.
     */
    public function createIndexMapping()
    {
        $this->createIndex();
        $this->createProductIndexMapping();
        $this->createCategoryIndexMapping();
        $this->createManufacturerIndexMapping();
        $this->createBrandIndexMapping();
        $this->refresh();
    }

    /**
     * Create product index mapping.
     */
    private function createProductIndexMapping()
    {
        $productMapping = new Mapping();
        $productMapping->setType($this->getType(Model::PRODUCT));
        $productMapping->setProperties([
            'user' => ['type' => 'keyword', 'include_in_all' => false],
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'family' => ['type' => 'keyword', 'include_in_all' => false],
            'ean' => ['type' => 'keyword', 'boost' => 10],
            'name' => ['type' => 'text', 'index' => false],
            'sortable_name' => ['type' => 'keyword'],
            'description' => ['type' => 'text', 'index' => false],
            'long_description' => ['type' => 'text', 'index' => false],
            'price' => ['type' => 'integer', 'include_in_all' => false],
            'reduced_price' => ['type' => 'integer', 'include_in_all' => false],
            'real_price' => ['type' => 'integer', 'include_in_all' => false],
            'discount' => ['type' => 'integer', 'include_in_all' => false],
            'discount_percentage' => ['type' => 'integer', 'include_in_all' => false],
            'image' => ['type' => 'keyword', 'include_in_all' => false],
            'updated_at' => ['type' => 'date'],
            'manufacturer' => [
                'type' => 'object',
                'dynamic' => 'strict',
                'include_in_all' => false,
                'properties' => [
                    'name' => [
                        'type' => 'keyword',
                    ],
                    'id' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'brand' => [
                'type' => 'object',
                'dynamic' => 'strict',
                'include_in_all' => false,
                'properties' => [
                    'name' => [
                        'type' => 'keyword',
                    ],
                    'id' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'categories' => [
                'type' => 'nested',
                'dynamic' => 'strict',
                'include_in_all' => false,
                'properties' => [
                    'name' => [
                        'type' => 'keyword',
                    ],
                    'id' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'tags' => [
                'type' => 'nested',
                'dynamic' => 'strict',
                'include_in_all' => false,
                'properties' => [
                    'name' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
            'second_level_searchable_data' => ['type' => 'text', 'boost' => 7, 'include_in_all' => true],
        ]);

        $productMapping->send();
    }

    /**
     * Create category index mapping.
     */
    private function createCategoryIndexMapping()
    {
        $categoryMapping = new Mapping();
        $categoryMapping->setType($this->getType(Model::CATEGORY));
        $categoryMapping->setProperties([
            'user' => ['type' => 'keyword', 'include_in_all' => false],
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $categoryMapping->send();
    }

    /**
     * Create manufacturer index mapping.
     */
    private function createManufacturerIndexMapping()
    {
        $manufacturerMapping = new Mapping();
        $manufacturerMapping->setType($this->getType(Model::MANUFACTURER));
        $manufacturerMapping->setProperties([
            'user' => ['type' => 'keyword', 'include_in_all' => false],
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $manufacturerMapping->send();
    }

    /**
     * Create brand index mapping.
     */
    private function createBrandIndexMapping()
    {
        $brandMapping = new Mapping();
        $brandMapping->setType($this->getType(Model::BRAND));
        $brandMapping->setProperties([
            'user' => ['type' => 'keyword', 'include_in_all' => false],
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $brandMapping->send();
    }
}
