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

namespace Apisearch\Server\Domain\QueryHandler;

use Apisearch\Server\Domain\Query\CheckIndex;
use Apisearch\Server\Domain\WithRepository;

/**
 * Class CheckIndexHandler.
 */
class CheckIndexHandler extends WithRepository
{
    /**
     * Check the index.
     *
     * @param CheckIndex $checkIndex
     *
     * @return bool
     */
    public function handle(CheckIndex $checkIndex): bool
    {
        $repositoryReference = $checkIndex->getRepositoryReference();

        $this
            ->repository
            ->setRepositoryReference($repositoryReference);

        return $this
            ->repository
            ->checkIndex();
    }
}
