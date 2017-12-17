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
 * Class CreateIndex.
 */
class CreateIndex implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * @var null|string
     *
     * Language
     */
    private $language;

    /**
     * ResetCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param null|string         $language
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        ? string $language
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->language = $language;
    }

    /**
     * Get Language.
     *
     * @return null|string
     */
    public function getLanguage(): ? string
    {
        return $this->language;
    }
}
