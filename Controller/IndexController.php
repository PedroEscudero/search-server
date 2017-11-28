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
use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Model\Item;
use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Server\Domain\Command\Index as IndexCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidFormatException;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;

/**
 * Class IndexController.
 */
class IndexController extends Controller
{
    /**
     * Add objects.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     * @throws InvalidKeyException
     */
    public function index(Request $request)
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
            ->handle(new IndexCommand(
                $query->get(HttpRepository::APP_ID_FIELD, ''),
                array_map(function (array $object) {
                    return Item::createFromArray($object);
                }, json_decode($items, true))
            ));

        return new JsonResponse('Items indexed', 200);
    }
}
