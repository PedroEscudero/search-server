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
        $this->assertNotExistsEventsIndex();
        $this->assertNotExistsLogsIndex();

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
     * Test create index command with events.
     */
    public function testCreateIndexWithEventsCommand()
    {
        $this->assertNotExistsIndex();
        $this->assertNotExistsEventsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:create-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-events' => true,
            ]
        ));

        $this->assertExistsIndex();
        $this->assertExistsEventsIndex();
        $this->assertNotExistsLogsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:delete-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-events' => true,
            ]
        ));

        $this->assertNotExistsIndex();
        $this->assertNotExistsEventsIndex();
    }

    /**
     * Test create index command with logs.
     */
    public function testCreateIndexWithLogsCommand()
    {
        $this->assertNotExistsIndex();
        $this->assertNotExistsLogsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:create-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-logs' => true,
            ]
        ));

        $this->assertExistsIndex();
        $this->assertNotExistsEventsIndex();
        $this->assertExistsLogsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:delete-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-logs' => true,
            ]
        ));

        $this->assertNotExistsIndex();
        $this->assertNotExistsLogsIndex();
    }

    /**
     * Test create all indices command.
     */
    public function testCreateAllIndicesCommand()
    {
        $this->assertNotExistsIndex();
        $this->assertNotExistsEventsIndex();
        $this->assertNotExistsLogsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:create-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-events' => true,
                '--with-logs' => true,
            ]
        ));

        $this->assertExistsIndex();
        $this->assertExistsEventsIndex();
        $this->assertExistsLogsIndex();

        static::$application->run(new ArrayInput(
            [
                'command' => 'apisearch:delete-index',
                '--app-id' => self::TEST_APP_ID,
                '--index' => self::TEST_INDEX,
                '--with-events' => true,
                '--with-logs' => true,
            ]
        ));

        $this->assertNotExistsIndex();
        $this->assertNotExistsEventsIndex();
        $this->assertNotExistsLogsIndex();
    }

    /**
     * Assert index exists.
     */
    protected function assertExistsIndex()
    {
        $this->assertTrue(
            $this->checkIndex(
                self::TEST_APP_ID,
                self::TEST_INDEX
            )
        );
    }

    /**
     * Assert index not exists.
     */
    protected function assertNotExistsIndex()
    {
        $this->assertFalse(
            $this->checkIndex(
                self::TEST_APP_ID,
                self::TEST_INDEX
            )
        );
    }

    /**
     * Assert index exists.
     */
    protected function assertExistsEventsIndex()
    {
        $this->queryEvents(
            Query::createMatchAll(),
            null,
            null,
            self::TEST_APP_ID,
            self::TEST_INDEX
        );
    }

    /**
     * Assert index not exists.
     */
    protected function assertNotExistsEventsIndex()
    {
        try {
            $this->assertExistsEventsIndex();
            $this->fail('Events index should not exist');
        } catch (Exception $e) {
            // OK
        }
    }

    /**
     * Assert index exists.
     */
    protected function assertExistsLogsIndex()
    {
        $this->queryEvents(
            Query::createMatchAll(),
            null,
            null,
            self::TEST_APP_ID,
            self::TEST_INDEX
        );
    }

    /**
     * Assert index not exists.
     */
    protected function assertNotExistsLogsIndex()
    {
        try {
            $this->assertExistsLogsIndex();
            $this->fail('Logs index should not exist');
        } catch (Exception $e) {
            // OK
        }
    }
}
