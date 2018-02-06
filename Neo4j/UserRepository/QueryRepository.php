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

namespace Apisearch\Server\Neo4j\UserRepository;

use Apisearch\Model\ItemUUID;
use Apisearch\Model\User;
use Apisearch\Server\Domain\Repository\UserRepository\QueryRepository as BaseQueryRepository;
use GraphAware\Common\Result\Record;

/**
 * Class QueryRepository.
 */
class QueryRepository extends Neo4jRepository implements BaseQueryRepository
{
    /**
     * Get interactions.
     *
     * @param User $user
     * @param int  $n
     *
     * @return ItemUUID[]
     */
    public function getInteractions(
        User $user,
        int $n
    ): array {
        $userId = $user->getId();
        $appId = $this->getAppId();
        $query = "
                MATCH (us:User)-[rel:interacts]->(it:Item)
                WHERE us.id = '{$userId}' AND us.app = '{$appId}' AND it.app = '{$appId}'
                RETURN it
                ORDER BY rel.w DESC
            ";

        $results = $this->runQuery($query);
        $records = $results->records();

        return array_map(function (Record $record) {
            return ItemUUID::createByComposedUUID($record->valueByIndex(0)->id);
        }, $records);
    }
}
