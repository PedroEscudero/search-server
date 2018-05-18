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
 * Class HighlightTest.
 */
trait HighlightTest
{
    /**
     * Test that highlight.
     */
    public function testBasic()
    {
        $result = $this->query(
            Query::create('v')
                ->enableHighlights()
        );

        $this->assertEquals(
            'Code da <em>vinci</em>',
            $result->getFirstItem()->getHighlight('title')
        );
    }

    /**
     * Test that highlight is not enabled when searchable_metadata is not stored.
     */
    public function testWithSearchableMetadataNotStored()
    {
        self::changeConfig([
            'store_searchable_metadata' => false,
        ]);

        $result = $this->query(
            Query::create('v')
                ->enableHighlights()
        );

        $this->assertNull(
            $result->getFirstItem()->getHighlight('title')
        );
    }
}
