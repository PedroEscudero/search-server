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

use Apisearch\Model\User;
use Apisearch\Query\Query;
use Apisearch\Server\Domain\Plugins;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;

/**
 * Class MachineLearningTest.
 */
trait MachineLearningTest
{
    /**
     * Create machine learning plugin token.
     *
     * @return Token
     */
    private function genereateMachineLearningPluginToken()
    {
        $token = new Token(TokenUUID::createById('dafc924e-6791-fad1-0969-54ec9a1e04c9'), self::$appId);
        $token->setPlugins([Plugins::MACHINE_LEARNING_BASIC]);
        $this->addToken($token);

        return $token;
    }

    /**
     * Create user.
     *
     * @return User
     */
    private function genereateUser()
    {
        return new User('1');
    }

    /**
     * Test basic behavior.
     */
    public function testBasicMLBehavior()
    {
        $this->markTestSkipped('Enable Neo4j');
        $token = $this->genereateMachineLearningPluginToken();

        // Simple query
        $result = $this->query(
            Query::createMatchAll()->byUser($this->genereateUser()),
            self::$appId,
            self::$index,
            $token
        );

        // Simple behavior
        $this->assertEquals('1~product', $result->getFirstItem()->getUUID()->composeUUID());

        /*
         * We emulate a click
         */
        $this->addInteraction(
            $this->genereateUser()->getId(),
            '3~book',
            1,
            self::$appId,
            $token
        );

        // Simple behavior
        $this->assertEquals(
            '3~book',
            $this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index,
                $token
            )->getFirstItem()->getUUID()->composeUUID()
        );

        /*
         * We emulate a buy
         */
        $this->addInteraction(
            $this->genereateUser()->getId(),
            '2~product',
            10,
            self::$appId,
            $token
        );

        // Simple behavior
        $this->assertEquals(
            '2~product',
            $this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index,
                $token
            )->getFirstItem()->getUUID()->composeUUID()
        );

        $it = 0;
        while ($it < 10) {
            $this->addInteraction(
                $this->genereateUser()->getId(),
                '3~book',
                1,
                self::$appId,
                $token
            );
            ++$it;
        }

        // Simple behavior
        $this->assertEquals(
            '3~book',
            $this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index,
                $token
            )->getFirstItem()->getUUID()->composeUUID()
        );

        $this->addInteraction(
            $this->genereateUser()->getId(),
            '2~product',
            2,
            self::$appId,
            $token
        );

        $this->addInteraction(
            $this->genereateUser()->getId(),
            '7~product',
            2,
            self::$appId,
            $token
        );

        $this->addInteraction(
            'another-user',
            '2~product',
            2,
            self::$appId,
            $token
        );

        // Simple behavior
        $this->assertEquals(
            '2~product',
            $this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index,
                $token
            )->getFirstItem()->getUUID()->composeUUID()
        );
    }

    /**
     * Test items promoted flag.
     */
    public function testItemsPromotedFlag()
    {
        $this->markTestSkipped('Enable Neo4j');
        $this->assertFalse($this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index
            )->getFirstItem()->isPromoted()
        );

        $this->assertTrue($this->query(
                Query::createMatchAll()->byUser($this->genereateUser()),
                self::$appId,
                self::$index,
                $this->genereateMachineLearningPluginToken()
            )->getFirstItem()->isPromoted()
        );
    }
}
