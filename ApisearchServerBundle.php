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
use Apisearch\Server\DependencyInjection\ApisearchServerExtension;
use Apisearch\Server\DependencyInjection\CompilerPass\DomainEventsMiddlewareCompilerPass;
use Apisearch\Server\DependencyInjection\CompilerPass\ElasticaConfigPathCompilerPass;
use Apisearch\Server\DependencyInjection\CompilerPass\ElasticsearchRepositoriesCompilerPass;
use League\Tactician\Bundle\TacticianBundle;
use Mmoreram\BaseBundle\BaseBundle;
use RSQueueBundle\RSQueueBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class PuntmigSearchServerBundle.
 */
class ApisearchServerBundle extends BaseBundle
{
    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     *
     * @throws \LogicException
     */
    public function getContainerExtension()
    {
        return new ApisearchServerExtension();
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

    /**
     * Return a CompilerPass instance array.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses(): array
    {
        return [
            new ElasticsearchRepositoriesCompilerPass(),
            new ElasticaConfigPathCompilerPass(),
            new DomainEventsMiddlewareCompilerPass(),
        ];
    }
}
