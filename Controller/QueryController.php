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

use Puntmig\Search\Query\Query as QueryModel;
use Puntmig\Search\Server\Domain\Exception\InvalidFormatException;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\Query\Query;

/**
 * Class QueryController.
 */
class QueryController extends Controller
{
    /**
     * Make a query.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidFormatException
     * @throws InvalidKeyException
     */
    public function query(Request $request)
    {
        $plainQuery = $request->get('query', null);
        if (!is_string($plainQuery)) {
            throw new InvalidFormatException();
        }

        $this->checkToken(
            $request,
            $request->get('app_id', ''),
            $request->get('key', '')
        );

        return new JsonResponse(
            $this
            ->commandBus
            ->handle(new Query(
                $request->get('app_id', ''),
                QueryModel::createFromArray(json_decode($plainQuery, true))
            ))
            ->toArray(),
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );
    }
}
