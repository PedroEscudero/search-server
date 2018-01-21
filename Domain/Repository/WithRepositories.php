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

namespace Apisearch\Server\Domain\Repository;

use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;

/**
 * Class WithRepositories.
 */
trait WithRepositories
{
    /**
     * @var WithRepositoryReference[]
     *
     * Repositories
     */
    private $repositories = [];

    /**
     * Add repository.
     *
     * @param WithRepositoryReference $repository
     */
    public function addRepository(WithRepositoryReference $repository)
    {
        $this->repositories[] = $repository;
    }

    /**
     * Get repository by class.
     *
     * @param string $class
     *
     * @return WithRepositoryReference
     */
    private function getRepository(string $class)
    {
        foreach ($this->repositories as $repository) {
            if ($repository instanceof $class) {
                return $repository;
            }
        }
    }

    /**
     * Set repository reference.
     *
     * @param RepositoryReference $repositoryReference
     */
    public function setRepositoryReference(RepositoryReference $repositoryReference)
    {
        parent::setRepositoryReference($repositoryReference);

        foreach ($this->repositories as $repository) {
            $repository->setRepositoryReference($repositoryReference);
        }
    }
}
