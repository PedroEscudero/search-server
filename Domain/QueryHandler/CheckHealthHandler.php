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

use Apisearch\Server\Domain\Query\CheckHealth;
use Elastica\Client;

/**
 * Class CheckHealthHandler.
 */
class CheckHealthHandler
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
     * Check the cluster.
     *
     * @param CheckHealth $checkHealth
     *
     * @return array
     */
    public function handle(CheckHealth $checkHealth): array
    {
        $health = $this
            ->client
            ->getCluster()
            ->getHealth();

        return [
            'status' => $health->getStatus(),
        ];
    }
}
