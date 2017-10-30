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

namespace Puntmig\Search\Server\Elastica\Repository;

use Puntmig\Search\Model\ItemUUID;
use Puntmig\Search\Server\Elastica\ElasticaWithAppIdWrapper;
use Puntmig\Search\Server\Elastica\ElasticaWrapper;

/**
 * Class DeleteRepository.
 */
class DeleteRepository extends ElasticaWithAppIdWrapper
{
    /**
     * Delete items.
     *
     * @param ItemUUID[] $itemUUIDs
     */
    public function deleteItems(array $itemUUIDs)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->appId,
                ElasticaWrapper::ITEM_TYPE
            )
            ->deleteIds(
                array_map(function (ItemUUID $itemUUID) {
                    return $itemUUID->composeUUID();
                }, $itemUUIDs)
            );

        $this->refresh();
    }
}
