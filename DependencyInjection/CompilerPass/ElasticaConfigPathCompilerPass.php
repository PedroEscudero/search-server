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

namespace Apisearch\Server\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ElasticaConfigPathCompilerPass.
 */
class ElasticaConfigPathCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $elasticRepositoryConfig = $container->getParameter('apisearch_server.config.repository');
        $elasticRepositoryConfigPath = $elasticRepositoryConfig['config_path'];
        $elasticRepositoryConfigPath = str_replace(
            '{root}',
            realpath($container->getParameter('kernel.root_dir').'/../'),
            $elasticRepositoryConfigPath
        );
        $elasticRepositoryConfig['config_path'] = $elasticRepositoryConfigPath;
        $container->setParameter('apisearch_server.config.repository', $elasticRepositoryConfig);
    }
}
