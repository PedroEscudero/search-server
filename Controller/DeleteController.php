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

use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Server\Domain\Command\Delete as DeleteCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidFormatException;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;

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
        $items = $request->get('items', null);
        if (!is_string($items)) {
            throw new InvalidFormatException();
        }

        $this->checkToken(
            $request,
            $request->get('app_id', ''),
            $request->get('key', '')
        );

        $this
            ->commandBus
            ->handle(new DeleteCommand(
                $request->get('app_id', ''),
                array_map(function (array $object) {
                    return ItemUUID::createFromArray($object);
                }, json_decode($items, true))
            ));

        return new JsonResponse('Items deleted', 200);
    }
}
