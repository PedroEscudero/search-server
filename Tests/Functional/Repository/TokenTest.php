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

namespace Apisearch\Server\Tests\Functional\Repository;

use Apisearch\Query\Query;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class TokenTest.
 */
trait TokenTest
{
    /**
     * Test token creation.
     */
    public function testTokenCreation()
    {
        $token = new Token(TokenUUID::createById('12345'));
        $this->addToken($token);
        $this->deleteToken(TokenUUID::createById('12345'));
    }

    /**
     * Test token without index permissions.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     */
    public function testTokenWithoutIndexPermissions()
    {
        $token = new Token(
            TokenUUID::createById('12345'),
            [self::$anotherIndex]
        );
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            '12345'
        );
    }

    /**
     * Test token without endpoint permissions.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     * @dataProvider dataTokenWithoutEndpointPermissionsFailing
     */
    public function testTokenWithoutEndpointPermissionsFailing(array $routes)
    {
        $token = new Token(TokenUUID::createById('12345'));
        $token->setEndpoints($routes);
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            '12345'
        );
    }

    /**
     * Data for testTokenWithoutEndpointPermissionsFailing.
     *
     * @return []
     */
    public function dataTokenWithoutEndpointPermissionsFailing()
    {
        return [
            [['get~~v1/events']],
            [['post~~v1']],
            [['post~~v1', 'post~~v1/']],
            [['get~~v1/events', 'post~~v1']],
            [['get~~v1/non-existing', 'post~~v1']],
        ];
    }

    /**
     * Test token without endpoint permissions.
     *
     * @dataProvider dataTokenWithoutEndpointPermissionsAccepted
     */
    public function testTokenWithoutEndpointPermissionsAccepted(array $routes)
    {
        $token = new Token(TokenUUID::createById('12345'));
        $token->setEndpoints($routes);
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            '12345'
        );
    }

    /**
     * Data for testTokenWithoutEndpointPermissionsAccepted.
     *
     * @return []
     */
    public function dataTokenWithoutEndpointPermissionsAccepted()
    {
        return [
            [[]],
            [['get~~v1']],
            [['get~~/v1']],
            [['get~~v1/']],
            [['get~~/v1/']],
            [['get~~/v1', 'post~~v1/']],
            [['get~~v1', 'get~~/v1']],
            [['get~~v1/items', 'get~~v1', '']],
            [['get~~v1/events', 'get~~v1', 'get~~/v1', '']],
        ];
    }

    /**
     * Test seconds available.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     */
    public function testSecondsAvailableFailing()
    {
        $token = new Token(TokenUUID::createById('12345'));
        $token->setSecondsValid(1);
        $this->addToken($token, self::$appId);
        sleep(1);
        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            '12345'
        );
    }

    /**
     * Test seconds available.
     */
    public function testSecondsAvailableAccepted()
    {
        $token = new Token(TokenUUID::createById('12345'));
        $token->setSecondsValid(1);
        $this->addToken($token, self::$appId);
        sleep(2);
        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            '12345'
        );
    }
}
