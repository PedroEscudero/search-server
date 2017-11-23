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

namespace Puntmig\Search\Server\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Event\Event;
use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\Query\ListEvents;
use Puntmig\Search\Server\Domain\Query\StatsEvents;

/**
 * Class EventsController.
 */
class EventsController extends Controller
{
    /**
     * List events.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidKeyException
     */
    public function list(Request $request)
    {
        $this->checkToken($request);
        $query = $request->query;

        $events = $this
            ->commandBus
            ->handle(new ListEvents(
                $query->get(HttpRepository::APP_ID_FIELD, ''),
                $query->get('name', ''),
                $this->castToIntIfNotNull($query, 'from'),
                $this->castToIntIfNotNull($query, 'to'),
                $this->castToIntIfNotNull($query, 'length'),
                $this->castToIntIfNotNull($query, 'offset')
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
     * @throws InvalidKeyException
     */
    public function stats(Request $request)
    {
        $this->checkToken($request);
        $query = $request->query;

        $stats = $this
            ->commandBus
            ->handle(new StatsEvents(
                $query->get(HttpRepository::APP_ID_FIELD, ''),
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
