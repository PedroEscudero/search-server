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

use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Repository\WithTokenTrait;
use Apisearch\Token\Token;

/**
 * Class QueryEvents.
 */
class QueryEvents implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;
    use WithTokenTrait;

    /**
     * @var Query
     *
     * Query
     */
    private $query;

    /**
     * @var int|null
     *
     * From
     */
    private $from;

    /**
     * @var int|null
     *
     * To
     */
    private $to;

    /**
     * DeleteCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param Query               $query
     * @param int|null            $from
     * @param int|null            $to
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token              $token,
        Query $query,
        ?int $from,
        ?int $to
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->token = $token;
        $this->query = $query;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get Query.
     *
     * @return Query
     */
    public function getQuery(): ? Query
    {
        return $this->query;
    }

    /**
     * Get From.
     *
     * @return int|null
     */
    public function getFrom(): ? int
    {
        return $this->from;
    }

    /**
     * Get To.
     *
     * @return int|null
     */
    public function getTo(): ? int
    {
        return $this->to;
    }
}
