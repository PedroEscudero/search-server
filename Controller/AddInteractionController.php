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

use Apisearch\Http\Http;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\AddInteraction;
use Apisearch\User\Interaction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddInteractionController.
 */
class AddInteractionController extends ControllerWithBus
{
    /**
     * Add an interaction.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addInteraction(Request $request): JsonResponse
    {
        $query = $request->query;

        return new JsonResponse('', 401);
        $this
            ->commandBus
            ->handle(new AddInteraction(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    ''
                ),
                $query->get('token'),
                Interaction::createFromArray(json_decode($query->get('interaction'), true))
            ));

        return new JsonResponse('', 200);
    }
}
