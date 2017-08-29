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
use Puntmig\Search\Server\Domain\Command\Index as IndexCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidFormatException;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\WithCommandBus;

/**
 * Class IndexController.
 */
class IndexController extends WithCommandBus
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
        $items = $request->get('items', null);
        if (!is_string($items)) {
            throw new InvalidFormatException();
        }

        $this
            ->commandBus
            ->handle(new IndexCommand(
                $request->get('key', ''),
                array_map(function (array $object) {
                    return Item::createFromArray($object);
                }, json_decode($items, true))
            ));

        return new JsonResponse('Items indexed', 200);
    }
}
