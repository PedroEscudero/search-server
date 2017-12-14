<?php

use Evenement\EventEmitter;
use React\Socket\ServerInterface;
use React\Socket\ConnectionInterface;

/**
 * The `LimitingServer` decorator wraps a given `ServerInterface` and is responsible
 * for limiting and keeping track of open connections to this server instance.
 *
 * Whenever the underlying server emits a `connection` event, it will check its
 * limits and then either
 * - keep track of this connection by adding it to the list of
 *   open connections and then forward the `connection` event
 * - or reject (close) the connection when its limits are exceeded and will
 *   forward an `error` event instead.
 *
 * Whenever a connection closes, it will remove this connection from the list of
 * open connections.
 *
 * ```php
 * $server = new LimitingServer($server, 100);
 * $server->on('connection', function (ConnectionInterface $connection) {
 *     $connection->write('hello there!' . PHP_EOL);
 *     …
 * });
 * ```
 *
 * See also the `ServerInterface` for more details.
 *
 * @see ServerInterface
 * @see ConnectionInterface
 */
class FiniteServer extends EventEmitter implements ServerInterface
{
    private $server;
    private $iterations = 0;
    private $maxIterations;

    /**
     * @param ServerInterface $server
     */
    public function __construct(ServerInterface $server, $maxIterations)
    {
        $this->server = $server;
        $this->maxIterations = $maxIterations;
        $this->server->on('connection', array($this, 'handleConnection'));
        $this->server->on('error', array($this, 'handleError'));
    }

    public function getAddress()
    {
        return $this->server->getAddress();
    }

    public function pause()
    {
        $this->server->pause();
    }

    public function resume()
    {
        $this->server->resume();
    }

    public function close()
    {
        $this->server->close();
    }

    /** @internal */
    public function handleConnection(ConnectionInterface $connection)
    {
        $this->iterations++;
        if ($this->iterations >= $this->maxIterations) {
            $connection->on('close', function() {
                $this->server->close();
            });
        }
        $this->emit('connection', array($connection));
    }

    /** @internal */
    public function handleError(\Exception $error)
    {
        $this->emit('error', array($error));
    }
}
