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

namespace Puntmig\Search\Server\Elastica;

use Elastica\Client;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type;
use Elastica\Type\Mapping;

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;

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
     * @param string $key
     *
     * @return Index
     */
    public function getSearchIndex(string $key) : Index
    {
        return $this
            ->client
            ->getIndex("indesky_$key");
    }

    /**
     * Create index.
     *
     * @param string $key
     * @param int    $shards
     * @param int    $replicas
     */
    public function createIndex(
        string $key,
        int $shards = 4,
        int $replicas = 1
    ) {
        $tagIndex = $this->getSearchIndex($key);
        $tagIndex->create([
            'number_of_shards' => $shards,
            'number_of_replicas' => $replicas,
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
     * @param string $key
     * @param string $typeName
     *
     * @return Type
     */
    public function getType(
        string $key,
        string $typeName
    ) {
        return $this
            ->getSearchIndex($key)
            ->getType($typeName);
    }

    /**
     * Search.
     *
     * @param string $key
     * @param Query  $query
     * @param int    $from
     * @param int    $size
     *
     * @return mixed
     */
    public function search(
        string $key,
        Query $query,
        int $from,
        int $size
    ) {
        $queryResult = $this
            ->getSearchIndex($key)
            ->search($query, [
                'from' => $from,
                'size' => $size,
            ]);

        return [
            'results' => $queryResult->getResults(),
            'aggregations' => $queryResult->getAggregations(),
            'total_hits' => $queryResult->getTotalHits(),
        ];
    }

    /**
     * Refresh.
     *
     * @param string $key
     */
    public function refresh(string $key)
    {
        $this
            ->getSearchIndex($key)
            ->refresh();
    }

    /**
     * Create mapping.
     *
     * @param string $key
     * @param int    $shards
     * @param int    $replicas
     */
    public function createIndexMapping(
        string $key,
        int $shards = 4,
        int $replicas = 1
    ) {
        $this->createIndex($key, $shards, $replicas);
        $this->createProductIndexMapping($key);
        $this->createCategoryIndexMapping($key);
        $this->createManufacturerIndexMapping($key);
        $this->createBrandIndexMapping($key);
        $this->createTagIndexMapping($key);
        $this->refresh($key);
    }

    /**
     * Create product index mapping.
     *
     * @param string $key
     */
    private function createProductIndexMapping(string $key)
    {
        $productMapping = new Mapping();
        $productMapping->setType($this->getType($key, Product::TYPE));
        $productMapping->setProperties([
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'family' => ['type' => 'keyword', 'include_in_all' => false],
            'ean' => ['type' => 'keyword', 'boost' => 10],
            'name' => ['type' => 'text', 'index' => false],
            'slug' => ['type' => 'text', 'index' => false],
            'sortable_name' => ['type' => 'keyword'],
            'stock' => ['type' => 'integer', 'index' => false],
            'description' => ['type' => 'text', 'index' => false],
            'long_description' => ['type' => 'text', 'index' => false],
            'price' => ['type' => 'float', 'include_in_all' => false],
            'reduced_price' => ['type' => 'float', 'include_in_all' => false],
            'real_price' => ['type' => 'float', 'include_in_all' => false],
            'discount' => ['type' => 'float', 'include_in_all' => false],
            'discount_percentage' => ['type' => 'integer', 'include_in_all' => false],
            'currency' => ['type' => 'keyword', 'include_in_all' => false],
            'image' => ['type' => 'keyword', 'include_in_all' => false],
            'rating' => ['type' => 'float', 'include_in_all' => false],
            'updated_at' => ['type' => 'date'],
            'coordinate' => ['type' => 'geo_point'],
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
                    'slug' => [
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
                    'slug' => [
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
                    'level' => [
                        'type' => 'integer',
                    ],
                    'slug' => [
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
     *
     * @param string $key
     */
    private function createCategoryIndexMapping(string $key)
    {
        $categoryMapping = new Mapping();
        $categoryMapping->setType($this->getType($key, Category::TYPE));
        $categoryMapping->setProperties([
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'slug' => ['type' => 'text', 'index' => false],
            'level' => ['type' => 'integer', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $categoryMapping->send();
    }

    /**
     * Create manufacturer index mapping.
     *
     * @param string $key
     */
    private function createManufacturerIndexMapping(string $key)
    {
        $manufacturerMapping = new Mapping();
        $manufacturerMapping->setType($this->getType($key, Manufacturer::TYPE));
        $manufacturerMapping->setProperties([
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'slug' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $manufacturerMapping->send();
    }

    /**
     * Create brand index mapping.
     *
     * @param string $key
     */
    private function createBrandIndexMapping(string $key)
    {
        $brandMapping = new Mapping();
        $brandMapping->setType($this->getType($key, Brand::TYPE));
        $brandMapping->setProperties([
            'id' => ['type' => 'keyword', 'include_in_all' => false],
            'name' => ['type' => 'text', 'index' => false],
            'slug' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $brandMapping->send();
    }

    /**
     * Create tag index mapping.
     *
     * @param string $key
     */
    private function createTagIndexMapping(string $key)
    {
        $tagMapping = new Mapping();
        $tagMapping->setType($this->getType($key, Tag::TYPE));
        $tagMapping->setProperties([
            'name' => ['type' => 'text', 'index' => false],
            'first_level_searchable_data' => ['type' => 'text', 'boost' => 10, 'include_in_all' => true],
        ]);

        $tagMapping->send();
    }
}
