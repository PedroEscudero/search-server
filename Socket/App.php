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

use Apisearch\Server\Domain\Token\TokenValidator;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class App.
 */
class App implements MessageComponentInterface
{
    /**
     * @var ConnectionsPool
     *
     * Connections
     */
    private $connectionsPool;

    /**
     * @var TokenValidator
     *
     * Token validator
     */
    private $tokenValidator;

    /**
     * @var string
     *
     * Element key
     */
    private $elementKey;

    /**
     * @var string
     *
     * Endpoint
     */
    private $endpoint;

    /**
     * App constructor.
     *
     * @param ConnectionsPool $connectionsPool
     * @param TokenValidator  $tokenValidator
     * @param string          $elementKey
     * @param string          $endpoint
     */
    public function __construct(
        ConnectionsPool $connectionsPool,
        TokenValidator $tokenValidator,
        string $elementKey,
        string $endpoint
    ) {
        $this->connectionsPool = $connectionsPool;
        $this->tokenValidator = $tokenValidator;
        $this->elementKey = $elementKey;
        $this->endpoint = $endpoint;
    }

    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $request = $conn->httpRequest;
        $uri = $request->getUri();
        $queryString = $uri->getQuery();
        parse_str($queryString, $query);

        $this
            ->tokenValidator
            ->validateToken(
                $query['app_id'],
                $query['index_id'],
                $query['token'],
                $request->getHeader('Origin')[0],
                $this->endpoint,
                'get'
            );

        $this
            ->connectionsPool
            ->addConnection(
                $conn,
                $query['app_id'],
                $query['index_id']
            );
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this
            ->connectionsPool
            ->removeConnection($conn);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method.
     *
     * @param ConnectionInterface $conn
     * @param \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }

    /**
     * Triggered when a client sends data through the socket.
     *
     * @param \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param string                       $msg  The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
    }

    /**
     * Write data into pool.
     *
     * We must ensure that both users connected to specific index and users
     * connected to all indices will receive this item properly
     *
     * @param array $payload
     */
    public function write(array $payload)
    {
        $this
            ->connectionsPool
            ->write(
                $payload['app_id'],
                $payload['index_id'],
                $payload[$this->elementKey]
            );

        $this
            ->connectionsPool
            ->write(
                $payload['app_id'],
                '*',
                $payload[$this->elementKey]
            );
    }
}
