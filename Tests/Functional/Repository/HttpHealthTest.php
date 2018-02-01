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

use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class HttpHealthTest.
 */
trait HttpHealthTest
{
    /**
     * Test ping with ping permission.
     */
    public function testPingWithPingPermissions()
    {
        $this->ping(new Token(TokenUUID::createById($this->getParameter('apisearch_server.ping_token')), self::$appId));
        $this->assertTrue(true);
    }

    /**
     * Test ping with invalid token.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     */
    public function testPingWithoutPermissions()
    {
        $this->ping(new Token(TokenUUID::createById('yyy'), self::$appId));
    }

    /**
     * Test check health with ping permission.
     */
    public function testCheckHealthWithPingPermissions()
    {
        $this->checkHealth(new Token(TokenUUID::createById($this->getParameter('apisearch_server.ping_token')), self::$appId));
        $this->assertTrue(true);
    }

    /**
     * Test check health with invalid token.
     *
     * @expectedException \Apisearch\Exception\InvalidTokenException
     */
    public function testCheckHealthWithoutPermissions()
    {
        $this->checkHealth(new Token(TokenUUID::createById('yyy'), self::$appId));
    }
}
