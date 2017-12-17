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

use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;

/**
 * Class CreateEventsIndex.
 */
class CreateEventsIndex implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * @var int
     *
     * Shards
     */
    private $shards;

    /**
     * @var int
     *
     * Replicas
     */
    private $replicas;

    /**
     * ResetCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param int                 $shards
     * @param int                 $replicas
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        int $shards,
        int $replicas
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->shards = $shards;
        $this->replicas = $replicas;
    }

    /**
     * Get shards.
     *
     * @return int
     */
    public function getShards(): int
    {
        return $this->shards;
    }

    /**
     * Get replicas.
     *
     * @return int
     */
    public function getReplicas(): int
    {
        return $this->replicas;
    }
}
