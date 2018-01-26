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
use Apisearch\Model\ItemUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\DeleteItems;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeleteItemsController.
 */
class DeleteItemsController extends ControllerWithBusAndEventRepository
{
    /**
     * Delete items.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     */
    public function deleteItems(Request $request): JsonResponse
    {
        $this->configureEventRepository($request);
        $query = $request->query;
        $requestBody = $request->request;

        $itemsUUID = $requestBody->get(Http::ITEMS_FIELD, null);
        if (!is_string($itemsUUID)) {
            throw InvalidFormatException::itemsUUIDRepresentationNotValid($itemsUUID);
        }

        $this
            ->commandBus
            ->handle(new DeleteItems(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                $query->get('token'),
                array_map(function (array $object) {
                    return ItemUUID::createFromArray($object);
                }, json_decode($itemsUUID, true))
            ));

        return new JsonResponse('Items deleted', 200);
    }
}
