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

use Puntmig\Search\Query\Query;

/**
 * Class SuggestTest.
 */
trait SuggestTest
{
    /**
     * Test basic suggest.
     */
    public function testBasicSuggest()
    {
        $repository = static::$repository;
        $results = $repository->query(
            Query::create('adi')
                ->enableSuggestions()
                ->disableAggregations()
        );

        $this->assertEquals(
            ['Adidas'],
            $results->getSuggests()
        );
    }
}
