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
use Apisearch\Query\ScoreStrategy;

/**
 * Class ScoreStrategyTest.
 */
trait ScoreStrategyTest
{
    /**
     * Test default strategy.
     */
    public function testDefaultStrategy()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategy(ScoreStrategy::createDefault())
        );

        $this->assertResults(
            $result,
            ['1', '2', '3', '4', '5']
        );
    }

    /**
     * Test relevance strategy.
     */
    public function testRelevanceStrategy()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategy(ScoreStrategy::createRelevanceBoosting())
        );

        $this->assertResults(
            $result,
            ['5', '{1', '4}', '3', '2']
        );
    }

    /**
     * Test custom function strategy.
     */
    public function testCustomFunctionStrategy()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategy(ScoreStrategy::createCustomFunction(
                    "doc['indexed_metadata.price'].value"
                ))
        );

        $this->assertResults(
            $result,
            ['3', '2', '1', '4', '5']
        );
    }
}
