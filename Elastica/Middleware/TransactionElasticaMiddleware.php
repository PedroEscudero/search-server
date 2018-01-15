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

namespace Apisearch\Server\Elastica\Middleware;

use Apisearch\Repository\Repository;
use Apisearch\Server\Domain\WriteCommand;
use League\Tactician\Middleware;

/**
 * Class TransactionElasticaMiddleware.
 */
class TransactionElasticaMiddleware implements Middleware
{
    /**
     * @var Repository
     *
     * Repository
     */
    protected $repository;

    /**
     * QueryHandler constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $result = $next($command);

        if ($command instanceof WriteCommand) {
            $this
                ->repository
                ->flush();
        }

        return $result;
    }
}
