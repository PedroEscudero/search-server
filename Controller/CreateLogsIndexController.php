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
use Apisearch\Server\Domain\Command\CreateLogsIndex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateLogsIndexController.
 */
class CreateLogsIndexController extends ControllerWithBus
{
    /**
     * Create a logs index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createLogsIndex(Request $request): JsonResponse
    {
        $query = $request->query;

        $this
            ->commandBus
            ->handle(new CreateLogsIndex(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                )
            ));

        return new JsonResponse('Logs index created', 200);
    }
}
