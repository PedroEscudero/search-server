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

use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\Reset as ResetCommand;
use Apisearch\Server\Domain\Exception\InvalidKeyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResetController.
 */
class ResetController extends Controller
{
    /**
     * Reset the index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidKeyException
     */
    public function reset(Request $request)
    {
        $this->configureEventRepository($request);
        $query = $request->query;
        $requestBody = $request->request;

        $this
            ->commandBus
            ->handle(new ResetCommand(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                $requestBody->get(HttpRepository::LANGUAGE_FIELD, null)
            ));

        return new JsonResponse('Index created', 200);
    }
}
