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

namespace Apisearch\Server\Domain;

use Apisearch\Repository\Repository;

/**
 * Class WithRepository.
 */
abstract class WithRepository
{
    /**
     * @var Repository
     *
     * Repository
     */
    protected $repository;

    /**
     * QueryHandler constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
}
