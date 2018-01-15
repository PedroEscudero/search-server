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

namespace Apisearch\Server\Domain\QueryHandler;

use Apisearch\Server\Domain\Query\Ping;
use Elastica\Client;

/**
 * Class PingHandler.
 */
class PingHandler
{
    /**
     * @var Client
     *
     * Elasticsearch Client
     */
    protected $client;

    /**
     * QueryHandler constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Ping.
     *
     * @param Ping $ping
     *
     * @return bool
     */
    public function handle(Ping $ping): bool
    {
        return $this->pingElasticsearch();
    }

    /**
     * Ping elasticsearch.
     */
    private function pingElasticsearch()
    {
        return 200 === $this
            ->client
            ->request('_cat/master')
            ->getStatus();
    }
}
