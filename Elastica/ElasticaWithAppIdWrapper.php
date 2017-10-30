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

namespace Puntmig\Search\Server\Elastica;

/**
 * Class ElasticaWithAppIdWrapper.
 */
abstract class ElasticaWithAppIdWrapper
{
    /**
     * @var ElasticaWrapper
     *
     * Elastica wrapper
     */
    protected $elasticaWrapper;

    /**
     * @var string
     *
     * App id
     */
    protected $appId;

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
     * Set app id.
     *
     * @param string $appId
     */
    public function setAppId(string $appId)
    {
        $this->appId = $appId;
    }

    /**
     * Refresh.
     */
    protected function refresh()
    {
        $this
            ->elasticaWrapper
            ->refresh($this->appId);
    }
}
