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

namespace Apisearch\Server\Domain\Middleware;

use Apisearch\Exception\InvalidTokenException;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\Server\Domain\Query\Query;
use League\Tactician\Middleware;

/**
 * Class TokenMiddleware.
 */
class TokenMiddleware implements Middleware
{
    /**
     * @param CommandWithRepositoryReferenceAndToken $command
     * @param callable                               $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $hasToken = ($command instanceof CommandWithRepositoryReferenceAndToken);
        if ($hasToken) {
            $this->applyMiddlewarePre($command);
        }

        $result = $next($command);

        return $result;
    }

    /**
     * Apply middleware pre.
     *
     * CommandWithRepositoryReferenceAndToken $command
     */
    private function applyMiddlewarePre(CommandWithRepositoryReferenceAndToken $command)
    {
        if ($command instanceof Query) {
            $this->checkMaxHitsPerQuery($command);
        }
    }

    /**
     * Check max hits per query.
     *
     * @param Query $query
     */
    private function checkMaxHitsPerQuery(Query $query)
    {
        $token = $query->getToken();

        if (
            $token->getMaxHitsPerQuery() > 0 &&
            $token->getMaxHitsPerQuery() < $query->getQuery()->getSize()
        ) {
            throw InvalidTokenException::createInvalidTokenMaxHitsPerQuery(
                $token->getTokenUUID()->composeUUID(),
                $token->getMaxHitsPerQuery()
            );
        }
    }
}
