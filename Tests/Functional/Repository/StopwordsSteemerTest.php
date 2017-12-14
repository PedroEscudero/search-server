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

/**
 * Class StopwordsSteemerTest.
 */
trait StopwordsSteemerTest
{
    /**
     * test finding without stopwords language.
     */
    public function testSearchWithoutStopwords()
    {
        $this->assertNotEmpty(
            $this->query(
                Query::create('de', 1, 1)
                    ->disableAggregations()
            )->getItems()
        );
    }

    /**
     * test finding without stopwords language.
     */
    public function testSearchWithtopwords()
    {
        /*
         * Reseting scenario for next calls.
         */
        self::resetScenario('es');
        $this->assertEmpty(
            $this->query(
                Query::create('de', 1, 1)
                    ->disableAggregations()
            )->getItems()
        );
    }
}
