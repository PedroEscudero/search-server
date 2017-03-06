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
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Puntmig\Search\Model\HttpTransportable;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Server\Core\IndexRepository;
use Puntmig\Search\Server\Core\QueryRepository;

/**
 * Class ApiController.
 */
class ApiController
{
    /**
     * @var QueryRepository
     *
     * Query repository
     */
    private $queryRepository;

    /**
     * @var IndexRepository
     *
     * Index repository
     */
    private $indexRepository;

    /**
     * @var string
     *
     * Key
     */
    private $key;

    /**
     * ServiceRepository constructor.
     *
     * @param QueryRepository $queryRepository
     * @param IndexRepository $indexRepository
     */
    public function __construct(
        QueryRepository $queryRepository,
        IndexRepository $indexRepository
    ) {
        $this->queryRepository = $queryRepository;
        $this->indexRepository = $indexRepository;
    }

    /**
     * Make a query.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function query(Request $request)
    {
        $query = $this->checkRequestQuality(
            $request,
            'query',
            'query'
        );

        if ($query instanceof Response) {
            return $query;
        }

        return new JsonResponse(
            $this
                ->queryRepository
                ->query(
                    $this->key,
                    Query::createFromArray($query)
                )
                ->toArray()
        );
    }

    /**
     * Add objects.
     *
     * @param Request $request
     * @param string  $parameterName
     * @param string  $objectNamespace
     * @param string  $method
     *
     * @return JsonResponse
     */
    public function index(
        Request $request,
        string $parameterName,
        string $objectNamespace,
        string $method
    ) {
        $objects = $this->checkRequestQuality(
            $request,
            'request',
            $parameterName
        );

        if ($objects instanceof Response) {
            return $objects;
        }

        /**
         * @var HttpTransportable $objectNamespace
         */
        $this
            ->indexRepository
            ->$method(
                array_map(function (array $object) use ($objectNamespace) {
                    return $objectNamespace::createFromArray($object);
                }, $objects)
            );

        return new JsonResponse('Objects indexed', 200);
    }

    /**
     * Reset the index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reset(Request $request)
    {
        $query = $this->checkRequestQuality(
            $request,
            'request'
        );

        if ($query instanceof Response) {
            return $query;
        }

        $this
            ->indexRepository
            ->createIndex();

        return new JsonResponse('Index created', 200);
    }

    /**
     * Check query quality.
     *
     * @param Request $request
     * @param string  $bagName
     * @param string  $parameterName
     *
     * @return array|JsonResponse
     */
    private function checkRequestQuality(
        Request $request,
        string $bagName,
        string $parameterName = null
    ) {
        /**
         * @var ParameterBag $bag
         */
        $bag = $request->$bagName;
        $key = $bag->get('key', null);

        if (is_null($key)) {
            return new JsonResponse([
                'message' => 'Invalid key',
            ], 401);
        }

        if (!in_array($key, [
            'mfdfd9fsjd', // Demo
            '5jk4j4kll4', // Laie
            'jkl4j4kl4k', // Jose Luis
            'hjk45hj4k4', // Test
            '5h43jk5h43', // Alternative Test

        ])) {
            return new JsonResponse([
                'message' => 'Key not found',
            ], 401);
        }

        $this->indexRepository->setKey($key);
        $this->key = $key;

        if (is_null($parameterName)) {
            return [];
        }

        $parameter = $bag->get($parameterName, null);

        if (is_null($parameter)) {
            return new JsonResponse([
                'message' => 'Invalid query',
            ], 400);
        }

        return $parameter;
    }
}
