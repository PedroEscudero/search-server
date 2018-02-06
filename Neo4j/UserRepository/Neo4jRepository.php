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

namespace Apisearch\Server\Neo4j\UserRepository;

use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use GraphAware\Bolt\Exception\IOException;
use GraphAware\Neo4j\Client\Client;

/**
 * Class Neo4jRepository.
 */
abstract class Neo4jRepository implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * @var Client
     *
     * Neo4j client
     */
    protected $client;

    /**
     * Neo4jRepository constructor.
     *
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Run query and return result.
     *
     * @param string $query
     *
     * @return mixed
     */
    protected function runQuery(string $query)
    {
        try {
            return $this
                ->client
                ->run($query);
        } catch (IOException $exception) {
            throw ResourceNotAvailableException::engineNotAvailable('Graphs');
        }
    }
}
