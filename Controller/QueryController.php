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
use Puntmig\Search\Repository\HttpRepository;
use Puntmig\Search\Server\Domain\Exception\InvalidFormatException;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\Query\Query;

/**
 * Class QueryController.
 */
class QueryController extends Controller
{
    /**
     * @var string
     *
     * Purge Query object from response
     */
    const PURGE_QUERY_FROM_RESPONSE_FIELD = 'incl_query';

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
        $this->configureEventRepository($request);
        $query = $request->query;

        $plainQuery = $query->get(HttpRepository::QUERY_FIELD, null);
        if (!is_string($plainQuery)) {
            throw new InvalidFormatException();
        }

        $responseAsArray = $this
            ->commandBus
            ->handle(new Query(
                $query->get(HttpRepository::APP_ID_FIELD, ''),
                QueryModel::createFromArray(json_decode($plainQuery, true))
            ))
            ->toArray();

        if ($query->has(self::PURGE_QUERY_FROM_RESPONSE_FIELD)) {
            unset($responseAsArray['query']);
        }

        $response = new JsonResponse(
            $responseAsArray,
            200,
            [
                'Access-Control-Allow-Origin' => '*',
            ]
        );

        return $response;
    }
}
