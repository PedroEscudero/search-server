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

namespace Apisearch\Server\Domain\Command;

use Apisearch\Model\ItemUUID;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;

/**
 * Class DeleteItems.
 */
class DeleteItems implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * @var ItemUUID[]
     *
     * Items UUID
     */
    private $itemsUUID;

    /**
     * DeleteCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param ItemUUID[]          $itemsUUID
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        array $itemsUUID
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->itemsUUID = $itemsUUID;
    }

    /**
     * Get Items.
     *
     * @return ItemUUID[]
     */
    public function getItemsUUID(): array
    {
        return $this->itemsUUID;
    }
}
