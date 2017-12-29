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

use Apisearch\Config\Config;
use Apisearch\Query\Query;

/**
 * Class SynonymsTest.
 */
trait SynonymsTest
{
    /**
     * Test synonyms.
     */
    public function testSynonyms()
    {
        $config = Config::createFromArray([
            'synonyms' => [
                ['words' => ['percebeiro', 'alfaguarra']],
            ],
        ]);

        $this->configureIndex($config);
        $result = $this->query(Query::create('alfaguarra'));
        $this->assertCount(1, $result->getItems());
        $this->assertEquals(1, $result->getFirstItem()->getId());
        $result = $this->query(Query::create('percebeiro'));
        $this->assertCount(1, $result->getItems());
        $this->assertEquals(1, $result->getFirstItem()->getId());
    }
}
