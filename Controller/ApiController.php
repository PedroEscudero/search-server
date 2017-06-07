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
use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Server\Core\DeleteRepository;
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
     * @var DeleteRepository
     *
     * Delete repository
     */
    private $deleteRepository;

    /**
     * @var string
     *
     * Key
     */
    private $key;

    /**
     * ServiceRepository constructor.
     *
     * @param QueryRepository  $queryRepository
     * @param IndexRepository  $indexRepository
     * @param DeleteRepository $deleteRepository
     */
    public function __construct(
        QueryRepository $queryRepository,
        IndexRepository $indexRepository,
        DeleteRepository $deleteRepository
    ) {
        $this->queryRepository = $queryRepository;
        $this->indexRepository = $indexRepository;
        $this->deleteRepository = $deleteRepository;
    }

    /**
     * Set key.
     *
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
        $this->queryRepository->setKey($key);
        $this->indexRepository->setKey($key);
        $this->deleteRepository->setKey($key);
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
                ->query(Query::createFromArray($query))
                ->toArray()
        );
    }

    /**
     * Add objects.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $objects = $this->checkRequestQuality(
            $request,
            'request',
            'items'
        );

        if ($objects instanceof Response) {
            return $objects;
        }

        /**
         * @var HttpTransportable $objectNamespace
         */
        $this
            ->indexRepository
            ->addItems(
                array_map(function (array $object) {
                    return Item::createFromArray($object);
                }, $objects)
            );

        return new JsonResponse('Items indexed', 200);
    }

    /**
     * Remove objects.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $objects = $this->checkRequestQuality(
            $request,
            'request',
            'items'
        );

        if ($objects instanceof Response) {
            return $objects;
        }

        /**
         * @var HttpTransportable $objectNamespace
         */
        $this
            ->deleteRepository
            ->deleteItems(
                array_map(function (array $object) {
                    return ItemUUID::createFromArray($object);
                }, $objects)
            );

        return new JsonResponse('Items deleted', 200);
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

        $this->setKey($key);

        if (is_null($parameterName)) {
            return [];
        }

        $parameter = $bag->get($parameterName, null);

        if (is_null($parameter)) {
            return new JsonResponse([
                'message' => 'Invalid query',
            ], 400);
        }

        return json_decode($parameter, true);
    }
}
