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
 * Class EventRepositoryPermissionsTest.
 */
trait EventRepositoryPermissionsTest
{
    /**
     * Test events list without permissions.
     *
     * @expectedException \Apisearch\Exception\ResourceNotAvailableException
     * @dataProvider dataEventsBadPermissions
     */
    public function testEventsBadPermissionsList($appId, $index)
    {
        $this->queryEvents(
            Query::createMatchAll(),
            1513470315000000,
            1513470315000000,
            $appId,
            $index
        );
    }

    /**
     * Test events stats without permissions.
     *
     * @expectedException \Apisearch\Exception\ResourceNotAvailableException
     * @dataProvider dataEventsBadPermissions
     */
    public function testEventsBadPermissionsStats($appId, $index)
    {
        $this->queryEvents(
            Query::createMatchAll(),
            1513470315000000,
            1513470315000000,
            $appId,
            $index
        );
    }

    /**
     * Data for some cases.
     *
     * @return array
     */
    public function dataEventsBadPermissions(): array
    {
        return [
            [self::$anotherAppId, self::$anotherIndex],
            [self::$anotherInexistentAppId, self::$index],
            [self::$anotherInexistentAppId, self::$anotherIndex],
        ];
    }

    /**
     * Reset all.
     */
    public function testResetAfterEventRepositoryPermissionTest()
    {
        $this->resetScenario();
    }
}
