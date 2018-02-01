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

use Apisearch\Server\Domain\Query\CheckHealth;
use Apisearch\Server\Domain\Query\Ping;
use Elastica\Cluster\Health;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HealthController.
 */
class HealthController extends ControllerWithBus
{
    /**
     * Health controller.
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        /**
         * @var array
         */
        $health = $this
            ->commandBus
            ->handle(new CheckHealth());

        return new JsonResponse($health);
    }

    /**
     * Ping.
     *
     * @return Response
     */
    public function ping(): Response
    {
        $alive = $this
            ->commandBus
            ->handle(new Ping());

        return true === $alive
            ? new Response('', Response::HTTP_OK)
            : new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
