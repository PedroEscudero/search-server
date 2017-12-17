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
 * Class RepositoryResetTest.
 */
trait RepositoryResetTest
{
    /**
     * Test reset repository.
     */
    public function testResetRepository()
    {
        $this->assertCount(
            5,
            $this->query(Query::createMatchAll())->getItems()
        );
        $this->resetIndex();
        $this->assertCount(
            0,
            $this->query(Query::createMatchAll())->getItems()
        );
    }

    /**
     * Reset all.
     */
    public function testResetAfterRepositoryResetTest()
    {
        $this->resetScenario();
    }
}
