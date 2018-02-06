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
use Apisearch\Model\ItemUUID;
use Apisearch\Query\Filter;
use Apisearch\Query\Query;

/**
 * Class CampaignBoostingTest.
 */
trait CampaignBoostingTest
{
    /**
     * Test default campaign.
     */
    public function testWithoutCampaign()
    {
        $this->markTestSkipped('Query text appliance must be solved when applied campaigns');
        $result = $this->query(
            Query::create('boosting')
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
        );
        $this->assertEquals(2, $result->getFirstItem()->getId());

        $result = $this->query(
            Query::create('anotherboosting')
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
        );
        $this->assertEquals(2, $result->getFirstItem()->getId());

        $result = $this->query(
            Query::createMatchAll()
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
                ->filterBy('_field', 'field_for_boosting_test', ['three'])
        );
        $this->assertEquals(2, $result->getFirstItem()->getId());
        $this->assertCount(2, $result->getItems());

        $result = $this->query(
            Query::createMatchAll()
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
                ->filterBy('_field', 'field_for_boosting_test', ['three', 'four'], Filter::AT_LEAST_ONE)
        );
        $this->assertEquals(2, $result->getFirstItem()->getId());
        $this->assertCount(4, $result->getItems());
    }

    /**
     * Test compaign when applying an specific query.
     */
    public function testCampaignBoostingWithQuery()
    {
        $this->markTestSkipped('Query text appliance must be solved when applied campaigns');
        $config = Config::createFromArray([
            'campaigns' => [
                [
                    'name' => 'test',
                    'enabled' => true,
                    'query_text' => 'boosting',
                    'boost_clauses' => [
                        [
                            'field' => 'simple_string',
                            'values' => ['aspirina'],
                            'boost' => 10.0,
                        ],
                    ],
                ],
            ],
        ]);

        $this->configureIndex($config);
        $result = $this->query(
            Query::create('boosting')
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
        );
        $this->assertEquals(5, $result->getFirstItem()->getId());

        $result = $this->query(
            Query::create('anotherboosting')
                ->promoteUUID(ItemUUID::createByComposedUUID('2~product'))
        );
        $this->assertEquals(2, $result->getFirstItem()->getId());
    }

    /**
     * Test simple campaign.
     */
    public function testCampaignBoosting()
    {
        $this->markTestSkipped('Query text appliance must be solved when applied campaigns');
        $config = Config::createFromArray([
            'campaigns' => [
                [
                    'name' => 'test',
                    'enabled' => true,
                    'boost_clauses' => [
                        [
                            'field' => 'simple_string',
                            'values' => ['aspirina'],
                            'boost' => 10.0,
                        ],
                    ],
                ],
            ],
        ]);

        $this->configureIndex($config);
        $result = $this->query(Query::createMatchAll());
        $this->assertEquals(5, $result->getFirstItem()->getId());
    }

    /**
     * Test compaign when applying an specific filter.
     *
     * @mark
     */
    public function testCampaignBoostingWithFilterApplied()
    {
        $this->markTestSkipped('Query text appliance must be solved when applied campaigns');
        $config = Config::createFromArray([
            'campaigns' => [
                [
                    'name' => 'test',
                    'enabled' => true,
                    'applied_filters' => [
                        'field_for_boosting_test' => 'three',
                    ],
                    'boost_clauses' => [
                        [
                            'field' => 'simple_string',
                            'values' => ['aspirina'],
                            'boost' => 10.0,
                        ],
                    ],
                ],
            ],
        ]);

        $this->configureIndex($config);
        $result = $this->query(Query::createMatchAll());
        $this->assertEquals(1, $result->getFirstItem()->getId());

        $result = $this->query(Query::createMatchAll()->filterBy('_field', 'other_field_for_boosting_test', ['three']));
        $this->assertEquals(2, $result->getFirstItem()->getId());

        $result = $this->query(Query::createMatchAll()->filterBy('_field', 'field_for_boosting_test', ['two']));
        $this->assertEquals(1, $result->getFirstItem()->getId());

        $result = $this->query(Query::create('boosting')->filterBy('_field', 'field_for_boosting_test', ['three']));
        $this->assertEquals(5, $result->getFirstItem()->getId());
        $this->assertCount(2, $result->getItems());

        $result = $this->query(Query::create('boosting')->filterBy('_field', 'field_for_boosting_test', ['three', 'four'], Filter::AT_LEAST_ONE));
        $this->assertEquals(5, $result->getFirstItem()->getId());
        $this->assertCount(2, $result->getItems());
        self::resetScenario();
    }
}
