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

use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Model\Item;
use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Query\Query;
use Puntmig\Search\Server\Domain\Command\DeleteCommand;
use Puntmig\Search\Server\Domain\Command\IndexCommand;
use Puntmig\Search\Server\Domain\Command\QueryCommand;
use Puntmig\Search\Server\Domain\Command\ResetCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\Exception\InvalidQueryException;
use Puntmig\Search\Server\Domain\Repository\QueryRepository;

/**
 * Class ApiController.
 */
class EventController
{
    /**
     * @var CommandBus
     *
     * Message bus
     */
    private $commandBus;

    /**
     * @var QueryRepository
     *
     * Query Repository
     */
    private $queryRepository;

    /**
     * ApiController constructor.
     *
     * @param CommandBus      $commandBus
     * @param QueryRepository $queryRepository
     */
    public function __construct(
        CommandBus $commandBus,
        QueryRepository $queryRepository
    ) {
        $this->commandBus = $commandBus;
        $this->queryRepository = $queryRepository;
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
        $query = $request->query;

        return $this->generateResponse(
            function () use ($query) {
                $plainQuery = $query->get('query', null);
                if (!is_string($plainQuery)) {
                    throw new InvalidQueryException();
                }

                return new JsonResponse(
                    $this
                    ->commandBus
                    ->handle(new QueryCommand(
                        $query->get('key', ''),
                        Query::createFromArray(json_decode($plainQuery, true))
                    ))
                    ->toArray(),
                    200
                );
            }
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
        $request = $request->request;

        return $this->generateResponse(
            function () use ($request) {
                $items = $request->get('items', null);
                if (!is_string($items)) {
                    throw new InvalidQueryException();
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
        );
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
        $request = $request->request;

        return $this->generateResponse(
            function () use ($request) {
                $items = $request->get('items', null);
                if (!is_string($items)) {
                    throw new InvalidQueryException();
                }

                $this
                    ->commandBus
                    ->handle(new DeleteCommand(
                        $request->get('key', ''),
                        array_map(function (array $object) {
                            return ItemUUID::createFromArray($object);
                        }, json_decode($items, true))
                    ));

                return new JsonResponse('Items deleted', 200);
            }
        );
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
        $request = $request->request;

        return $this->generateResponse(
            function () use ($request) {
                $this
                    ->commandBus
                    ->handle(new ResetCommand(
                        $request->get('key', ''),
                        $request->get('language', null)
                    ));

                return new JsonResponse('Items created', 200);
            }
        );
    }

    /**
     * Generate response.
     *
     * @param callable $callable
     *
     * @return JsonResponse
     */
    private function generateResponse(callable $callable) : JsonResponse
    {
        try {
            $response = $callable();
        } catch (InvalidKeyException $e) {
            return new JsonResponse([
                'message' => 'Invalid key',
            ], 401);
        } catch (InvalidQueryException $e) {
            return new JsonResponse([
                'message' => 'Invalid query',
                'trace' => $e->getMessage(),
            ], 400);
        }

        return $response;
    }
}
