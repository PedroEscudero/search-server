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

        try {
            Query::createFromArray($query);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage());
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

        return new JsonResponse([], 200);
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
        string $parameterName
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

        $this->indexRepository->setKey($key);
        $this->key = $key;
        $parameter = $bag->get($parameterName, null);

        if (is_null($parameter)) {
            return new JsonResponse([
                'message' => 'Invalid query',
            ], 400);
        }

        return $parameter;
    }
}
