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

namespace Apisearch\Server\Socket;

use Ratchet\ConnectionInterface;

/**
 * Class ConnectionsPool.
 */
class ConnectionsPool
{
    /**
     * Connections.
     */
    private $connections = [];

    /**
     * Connections references.
     */
    private $connectionReferences = [];

    /**
     * Add connection.
     *
     * @param ConnectionInterface $connection
     * @param string              $appId
     * @param string              $indexId
     */
    public function addConnection(
        ConnectionInterface $connection,
        string $appId,
        string $indexId
    ) {
        $key = "$appId~~$indexId";
        $connectionKey = spl_object_hash($connection);
        if (!array_key_exists($key, $this->connections)) {
            $this->connections[$key] = [];
        }

        $this->connections[$key][$connectionKey] = $connection;
        $this->connectionReferences[$connectionKey] = $key;
    }

    /**
     * Remove connection.
     *
     * @param ConnectionInterface $connection
     */
    public function removeConnection(ConnectionInterface $connection)
    {
        $connectionKey = spl_object_hash($connection);
        if (!array_key_exists($connectionKey, $this->connectionReferences)) {
            return;
        }

        $key = $this->connectionReferences[$connectionKey];
        if (
            !array_key_exists($key, $this->connections) ||
            !array_key_exists($connectionKey, $this->connections[$key])
        ) {
            return;
        }

        $this->connections[$key][$connectionKey] = null;
        unset($this->connections[$key][$connectionKey]);
    }

    /**
     * Write data into pool.
     *
     * @param string $appId
     * @param string $indexId
     * @param mixed  $element
     */
    public function write(
        string $appId,
        string $indexId,
        $element
    ) {
        $key = "$appId~~$indexId";

        if (!array_key_exists($key, $this->connections)) {
            return;
        }

        foreach ($this->connections[$key] as $connection) {
            $connection->send(json_encode($element));
        }
    }
}
