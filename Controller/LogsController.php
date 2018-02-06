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

namespace Apisearch\Server\Controller;

use Apisearch\Exception\InvalidFormatException;
use Apisearch\Http\Http;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Query\QueryLogs;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogsController.
 */
class LogsController extends ControllerWithBus
{
    /**
     * Query logs.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function query(Request $request): JsonResponse
    {
        $query = $request->query;

        $plainQuery = $query->get(Http::QUERY_FIELD, null);
        if (!is_string($plainQuery)) {
            throw InvalidFormatException::queryFormatNotValid(json_encode($plainQuery));
        }

        $eventsAsArray = $this
            ->commandBus
            ->handle(new QueryLogs(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                $query->get('token'),
                Query::createFromArray(json_decode($plainQuery, true)),
                $this->castToIntIfNotNull($query, Http::FROM_FIELD),
                $this->castToIntIfNotNull($query, Http::TO_FIELD)
            ))
            ->toArray();

        return new JsonResponse(
            $eventsAsArray,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
