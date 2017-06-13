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

use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query;

/**
 * Class ExcludeReferencesTest.
 */
trait ExcludeReferencesTest
{
    /**
     * Test family filter.
     */
    public function testExcludeProducts()
    {
        $repository = static::$repository;

        $this->assertResults(
            $repository->query(Query::createMatchAll()->excludeUUID(new ItemUUID('2', 'product'))),
            ['?1', '!2', '?3', '?4', '?5']
        );

        $this->assertResults(
            $repository->query(Query::createMatchAll()->excludeUUIDs([
                new ItemUUID('2', 'product'),
                new ItemUUID('3', 'book'),
                new ItemUUID('4', 'superbike'),
                new ItemUUID('6', 'boke'),
            ])),
            ['?1', '!2', '!3', '?4', '?5']
        );

        $this->assertEmpty(
            $repository->query(Query::create('engonga')->excludeUUID(new ItemUUID('3', 'book')))->getItems()
        );
    }
}
