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

use Apisearch\Model\ItemUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Elastica\Repository\ItemElasticaWrapper;

/**
 * Class DeletionTest.
 */
trait DeletionTest
{
    /**
     * Test item deletions.
     */
    public function testItemDeletions()
    {
        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertNbItems(4);

        $this->deleteItems([new ItemUUID('1', 'product')]);
        $this->assertNbItems(4);

        $this->deleteItems([new ItemUUID('75894379573', 'product')]);
        $this->assertNbItems(4);

        $this->deleteItems([new ItemUUID('5', 'product')]);
        $this->assertNbItems(4);

        $this->deleteItems([new ItemUUID('5', 'gum')]);
        $this->assertNbItems(3);

        /*
         * Reseting scenario for next calls.
         */
        self::resetScenario();
    }

    /**
     * Check nb items.
     *
     * @param int $nb
     */
    private function assertNbItems(int $nb)
    {
        $this->assertSame($nb, $this
            ->get('apisearch_server.item_elastica_wrapper')
            ->getType(
                RepositoryReference::create(
                    self::$appId,
                    self::$index
                ),
                ItemElasticaWrapper::ITEM_TYPE
            )->count()
        );
    }
}
