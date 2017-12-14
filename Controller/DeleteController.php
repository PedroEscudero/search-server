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

use Apisearch\Model\ItemUUID;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\Delete as DeleteCommand;
use Apisearch\Server\Domain\Exception\InvalidFormatException;
use Apisearch\Server\Domain\Exception\InvalidKeyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteController.
 */
class DeleteController extends Controller
{
    /**
     * Remove objects.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     * @throws InvalidKeyException
     */
    public function delete(Request $request)
    {
        $this->configureEventRepository($request);
        $query = $request->query;
        $requestBody = $request->request;

        $items = $requestBody->get(HttpRepository::ITEMS_FIELD, null);
        if (!is_string($items)) {
            throw new InvalidFormatException();
        }

        $this
            ->commandBus
            ->handle(new DeleteCommand(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                array_map(function (array $object) {
                    return ItemUUID::createFromArray($object);
                }, json_decode($items, true))
            ));

        return new JsonResponse('Items deleted', 200);
    }
}
