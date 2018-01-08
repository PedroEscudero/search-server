<?php
/**
 * File header placeholder
 */

namespace Apisearch\Server\Elastica;

use Apisearch\Repository\RepositoryReference;

/**
 * Class WithRepositories
 */
trait WithRepositories
{
    /**
     * @var ElasticaWrapperWithRepositoryReference[]
     *
     * Repositories
     */
    private $repositories = [];

    /**
     * Add repository.
     *
     * @param ElasticaWrapperWithRepositoryReference $repository
     */
    public function addRepository(ElasticaWrapperWithRepositoryReference $repository)
    {
        $this->repositories[] = $repository;
    }

    /**
     * Get repository by class.
     *
     * @param string $class
     *
     * @return ElasticaWrapperWithRepositoryReference
     */
    private function getRepository(string $class)
    {
        foreach ($this->repositories as $repository) {
            if (get_class($repository) === $class) {
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