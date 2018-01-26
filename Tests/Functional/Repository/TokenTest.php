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
        $token = new Token(TokenUUID::createById('12345'), self::$appId);
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
            self::$appId,
            [self::$anotherIndex]
        );
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
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
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );

        $token->setEndpoints($routes);
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
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
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );

        $token->setEndpoints($routes);
        $this->addToken($token, self::$appId);

        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
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
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );
        $token->setSecondsValid(1);
        $this->addToken($token, self::$appId);
        sleep(2);
        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
        );
    }

    /**
     * Test seconds available.
     */
    public function testSecondsAvailableAccepted()
    {
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );
        $token->setSecondsValid(2);
        $this->addToken($token, self::$appId);
        sleep(1);
        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
        );
    }

    /**
     * Test different app id.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     */
    public function testInvalidAppId()
    {
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );
        $this->addToken($token, self::$appId);
        $this->query(
            Query::createMatchAll(),
            self::$anotherAppId,
            self::$index,
            $token
        );
    }

    /**
     * Test max hits per query.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     * @expectedExceptionMessage Token 12345 not valid. Max 2 hits allowed
     */
    public function testMaxHitsPerQuery()
    {
        $token = new Token(
            TokenUUID::createById('12345'),
            self::$appId
        );
        $token->setMaxHitsPerQuery(2);
        $this->addToken($token, self::$appId);
        $this->query(
            Query::createMatchAll(),
            self::$appId,
            self::$index,
            $token
        );
        die();
    }
}
