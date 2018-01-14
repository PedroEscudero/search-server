<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Apisearch\Server\Tests\Functional\Repository;

use Apisearch\Model\Item;
use Apisearch\Query\Query;

/**
 * Class ErrorRecoveryTest
 */
trait ErrorRecoveryTest
{
    /**
     * Test server after fatal error
     */
    public function testAfterFatalError()
    {
        try {
            $this->indexItems([
                Item::createFromArray([
                    'uuid'             => [
                        'id'   => 6743,
                        'type' => 'product'
                    ],
                    'indexed_metadata' => [
                        'category' => 'lala'
                    ]
                ])
            ]);
        } catch (\Exception $exception) {
            // Silent pass
        }
        // At this point we should be able to make a simple query
        $this->assertCount(
            5,
            $this->query(Query::createMatchAll())->getItems()
        );
    }
}