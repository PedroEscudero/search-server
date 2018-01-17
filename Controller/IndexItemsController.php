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
use Apisearch\Model\Item;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\IndexItems;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IndexItemsController.
 */
class IndexItemsController extends ControllerWithBusAndEventRepository
{
    /**
     * Index items.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     */
    public function indexItems(Request $request): JsonResponse
    {
        $this->configureEventRepository($request);
        $query = $request->query;
        $requestBody = $request->request;
        $items = $requestBody->get(Http::ITEMS_FIELD, null);
        if (!is_string($items)) {
            throw InvalidFormatException::itemsRepresentationNotValid($items);
        }

        $this
            ->commandBus
            ->handle(new IndexItems(
                RepositoryReference::create(
                    $query->get(Http::APP_ID_FIELD),
                    $query->get(Http::INDEX_FIELD)
                ),
                array_map(function (array $object) {
                    return Item::createFromArray($object);
                }, json_decode($items, true))
            ));

        return new JsonResponse('Items indexed', 200);
    }
}
