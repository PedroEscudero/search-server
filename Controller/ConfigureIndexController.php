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

use Apisearch\Config\Config;
use Apisearch\Exception\InvalidFormatException;
use Apisearch\Http\Http;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\ConfigureIndex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ConfigureIndexController.
 */
class ConfigureIndexController extends ControllerWithBusAndEventRepository
{
    /**
     * Config the index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function configureIndex(Request $request): JsonResponse
    {
        $this->configureEventRepository($request);
        $query = $request->query;
        $requestBody = $request->request;

        $plainConfig = $requestBody->get(Http::CONFIG_FIELD, null);
        if (!is_string($plainConfig)) {
            throw InvalidFormatException::configFormatNotValid($plainConfig);
        }

        $this
            ->commandBus
            ->handle(new ConfigureIndex(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                $query->get('token'),
                Config::createFromArray(json_decode($plainConfig, true))
            ));

        return new JsonResponse('Config applied', 200);
    }
}
