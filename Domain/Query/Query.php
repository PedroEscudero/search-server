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

namespace Apisearch\Server\Domain\Query;

use Apisearch\Query\Query as SearchQuery;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Server\Domain\CommandWithRepositoryReference;

/**
 * Class Query.
 */
class Query implements CommandWithRepositoryReference
{
    use WithRepositoryReference;

    /**
     * @var SearchQuery
     *
     * Query
     */
    private $query;

    /**
     * DeleteCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param SearchQuery         $query
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        SearchQuery $query
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->query = $query;
    }

    /**
     * Get Query.
     *
     * @return SearchQuery
     */
    public function getQuery(): SearchQuery
    {
        return $this->query;
    }
}
