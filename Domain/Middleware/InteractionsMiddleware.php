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

namespace Apisearch\Server\Domain\Middleware;

use Apisearch\Model\ItemUUID;
use Apisearch\Model\User;
use Apisearch\Result\Result;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\Server\Domain\Plugins;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Server\Domain\Repository\UserRepository\QueryRepository;
use Apisearch\User\UserRepository;
use League\Tactician\Middleware;

/**
 * Class InteractionsMiddleware.
 */
class InteractionsMiddleware implements Middleware
{
    /**
     * @var UserRepository
     *
     * User repository
     */
    private $userRepository;

    /**
     * InteractionsMiddleware constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param CommandWithRepositoryReferenceAndToken $command
     * @param callable                               $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $hasPlugin = $command
            ->getToken()
            ->hasPlugin(Plugins::MACHINE_LEARNING_BASIC);

        $this
            ->userRepository
            ->setRepositoryReference($command->getRepositoryReference());

        $context = [];

        if ($hasPlugin) {
            $context = $this->applyMiddlewarePre($command);
        }

        $result = $next($command);

        if ($hasPlugin) {
            $this->applyMiddlewarePost(
                $command,
                $result,
                $context
            );
        }

        return $result;
    }

    /**
     * Apply middleware pre.
     *
     * CommandWithRepositoryReferenceAndToken $command
     */
    private function applyMiddlewarePre(CommandWithRepositoryReferenceAndToken $command)
    {
        if (
            $this->userRepository instanceof QueryRepository &&
            $command instanceof Query &&
            $command->getQuery()->getUser() instanceof User
        ) {
            $query = $command->getQuery();

            $itemUUIDs = $this
                ->userRepository
                ->getInteractions(
                    $query->getUser(),
                    $query->getSize()
                );

            if (!empty($itemUUIDs)) {
                foreach ($itemUUIDs as $itemUUID) {
                    $query->promoteUUID($itemUUID);
                }
            }

            return [
                'promoted_items_uuid' => array_map(function (ItemUUID $itemUUID) {
                    return $itemUUID->composeUUID();
                }, $itemUUIDs),
            ];
        }

        return [];
    }

    /**
     * Apply middleware post.
     *
     * @param CommandWithRepositoryReferenceAndToken $command
     * @param mixed|null                             $result
     * @param array                                  $context
     */
    private function applyMiddlewarePost(
        CommandWithRepositoryReferenceAndToken $command,
        $result,
        array $context
    ) {
        if (
            $command instanceof Query &&
            $result instanceof Result &&
            array_key_exists('promoted_items_uuid', $context)) {
            $resultItems = $result->getItems();
            foreach ($resultItems as $item) {
                if (in_array($item->getUUID()->composeUUID(), $context['promoted_items_uuid'])) {
                    $item->setPromoted();
                } else {
                    break;
                }
            }
        }
    }
}
