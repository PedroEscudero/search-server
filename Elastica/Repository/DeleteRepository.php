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

namespace Apisearch\Server\Elastica\Repository;

use Apisearch\Model\ItemUUID;
use Apisearch\Server\Domain\Repository\Repository\DeleteRepository as DeleteRepositoryInterface;
use Apisearch\Server\Elastica\ElasticaWrapperWithRepositoryReference;

/**
 * Class DeleteRepository.
 */
class DeleteRepository extends ElasticaWrapperWithRepositoryReference implements DeleteRepositoryInterface
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
            ->deleteDocumentsByIds(
                $this->getRepositoryReference(),
                array_map(function (ItemUUID $itemUUID) {
                    return $itemUUID->composeUUID();
                }, $itemUUIDs)
            );

        $this->refresh();
    }
}
