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

namespace Apisearch\Server\Elastica;

use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;

/**
 * Class ElasticaWithAppIdWrapper.
 */
abstract class ElasticaWrapperWithRepositoryReference implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * @var ElasticaWrapper
     *
     * Elastica wrapper
     */
    protected $elasticaWrapper;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ElasticaWrapper $elasticaWrapper
     */
    public function __construct(ElasticaWrapper $elasticaWrapper)
    {
        $this->elasticaWrapper = $elasticaWrapper;
    }

    /**
     * Refresh.
     */
    protected function refresh()
    {
        $this
            ->elasticaWrapper
            ->refresh($this->getRepositoryReference());
    }
}
