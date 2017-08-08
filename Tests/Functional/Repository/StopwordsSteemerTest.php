<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Tests\Functional\Repository;

use Puntmig\Search\Query\Query;

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
        $this->assertEmpty(
            $this->query(
                Query::create('alamo', 1, 1)
                    ->disableAggregations()
            )->getItems()
        );

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
        /**
         * Reseting scenario for next calls.
         */
        self::resetScenario('es');
        $this->assertNotEmpty(
            $this->query(
                Query::create('alamo', 1, 1)
                    ->disableAggregations()
            )->getItems()
        );

        $this->assertEmpty(
            $this->query(
                Query::create('de', 1, 1)
                    ->disableAggregations()
            )->getItems()
        );
    }
}
