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

namespace Apisearch\Server;

use Apisearch\ApisearchBundle;
use League\Tactician\Bundle\TacticianBundle;
use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\SimpleBaseBundle;
use RSQueueBundle\RSQueueBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PuntmigSearchServerBundle.
 */
class ApisearchServerBundle extends SimpleBaseBundle
{
    /**
     * get config files.
     *
     * @return array
     */
    public function getConfigFiles(): array
    {
        return [
            'domain',
            'controllers',
            'console',
            'elastica',
        ];
    }

    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel): array
    {
        return [
            ApisearchBundle::class,
            FrameworkBundle::class,
            MonologBundle::class,
            BaseBundle::class,
            RSQueueBundle::class,
            new TacticianBundle(),
        ];
    }
}
