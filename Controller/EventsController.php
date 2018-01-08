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
use Apisearch\Exception\InvalidTokenException;
use Apisearch\Http\Http;
use Apisearch\Query\Query;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Query\QueryEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EventsController.
 */
class EventsController extends ControllerWithBus
{
    /**
     * Query events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidTokenException
     */
    public function query(Request $request)
    {
        $query = $request->query;

        $plainQuery = $query->get(Http::QUERY_FIELD, null);
        if (!is_string($plainQuery)) {
            throw InvalidFormatException::queryFormatNotValid($plainQuery);
        }

        $eventsAsArray = $this
            ->commandBus
            ->handle(new QueryEvents(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                Query::createFromArray(json_decode($plainQuery, true)),
                $this->castToIntIfNotNull($query, Http::FROM_FIELD),
                $this->castToIntIfNotNull($query, Http::TO_FIELD)
            ))
            ->toArray();

        $response = new JsonResponse(
            $eventsAsArray,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );

        return $response;
    }

    /**
     * Get query value and cast to int of not null.
     *
     * @param ParameterBag $parameters
     * @param string       $paramName
     *
     * @return int|null
     */
    private function castToIntIfNotNull(
        ParameterBag $parameters,
        string $paramName
    ): ? int {
        $param = $parameters->get($paramName, null);
        if (!is_null($param)) {
            $param = intval($param);
        }

        return $param;
    }
}
