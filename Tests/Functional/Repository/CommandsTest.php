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

use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Query\Query;
use Apisearch\Server\Tests\Functional\ApisearchServerBundleFunctionalTest;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class CommandsTest.
 */
class CommandsTest extends ApisearchServerBundleFunctionalTest
{
    /**
     * @var string
     */
    const TEST_APP_ID = '12345-test';

    /**
     * @var string
     */
    const TEST_INDEX = '67890-test';

    /**
     * Save events.
     *
     * @return bool
     */
    protected static function saveEvents(): bool
    {
        return false;
    }

    /**
     * Save logs.
     *
     * @return bool
     */
    protected static function saveLogs(): bool
    {
        return false;
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        try {
            static::deleteIndex(self::TEST_APP_ID, self::TEST_INDEX);
        } catch (ResourceNotAvailableException $e) {
        }
    }

    /**
     * Test create index command.
     */
    public function testCreateIndexCommand()
    {
        $this->assertNotExistsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:create-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
            ]
        ));

        $this->assertExistsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:delete-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
            ]
        ));

        $this->assertNotExistsIndex();
    }

    /**
     * Assert exists.
     */
    private function assertExistsIndex()
    {
        $this->assertTrue(
            $this->checkIndex(
                self::TEST_APP_ID,
                self::TEST_INDEX
            )
        );
    }

    /**
     * Assert exists.
     */
    private function assertNotExistsIndex()
    {
        $this->assertFalse(
            $this->checkIndex(
                self::TEST_APP_ID,
                self::TEST_INDEX
            )
        );
    }
}
