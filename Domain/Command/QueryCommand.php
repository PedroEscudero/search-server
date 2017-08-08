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

namespace Puntmig\Search\Server\Domain\Command;

use Puntmig\Search\Query\Query;

/**
 * Class QueryCommand.
 */
class QueryCommand extends WithKeyCommand
{
    /**
     * @var Query
     *
     * Query
     */
    private $query;

    /**
     * DeleteCommand constructor.
     *
     * @param string $key
     * @param Query  $query
     */
    public function __construct(
        string $key,
        Query $query
    ) {
        $this->key = $key;
        $this->query = $query;
    }

    /**
     * Get Query.
     *
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }
}
