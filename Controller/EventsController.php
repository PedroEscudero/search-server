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

use Apisearch\Event\Event;
use Apisearch\Event\SortBy;
use Apisearch\Exception\InvalidTokenException;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Query\ListEvents;
use Apisearch\Server\Domain\Query\StatsEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EventsController.
 */
class EventsController extends ControllerWithBus
{
    /**
     * List events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidTokenException
     */
    public function list(Request $request)
    {
        $query = $request->query;

        $events = $this
            ->commandBus
            ->handle(new ListEvents(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                $query->get('name', ''),
                $this->castToIntIfNotNull($query, 'from'),
                $this->castToIntIfNotNull($query, 'to'),
                $this->castToIntIfNotNull($query, 'length'),
                $this->castToIntIfNotNull($query, 'offset'),
                $query->get('sort_by', SortBy::OCCURRED_ON_DESC)
            ));

        $events = array_map(function (Event $event) {
            return $event->toArray();
        }, $events);

        return new JsonResponse(
            $events,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }

    /**
     * Stats events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidTokenException
     */
    public function stats(Request $request)
    {
        $query = $request->query;

        $stats = $this
            ->commandBus
            ->handle(new StatsEvents(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                $this->castToIntIfNotNull($query, 'from'),
                $this->castToIntIfNotNull($query, 'to')
            ));

        return new JsonResponse(
            $stats->toArray(),
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
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
