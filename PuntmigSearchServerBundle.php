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

namespace Puntmig\Search\Server;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use League\Tactician\Bundle\TacticianBundle;
use Mmoreram\BaseBundle\CompilerPass\MappingCompilerPass;
use Mmoreram\BaseBundle\SimpleBaseBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\HttpKernel\KernelInterface;

use Puntmig\Search\PuntmigSearchBundle;
use Puntmig\Search\Server\Doctrine\MappingBagProvider;

/**
 * Class PuntmigSearchServerBundle.
 */
class PuntmigSearchServerBundle extends SimpleBaseBundle
{
    /**
     * get config files.
     *
     * @return array
     */
    public function getConfigFiles() : array
    {
        return [
            'domain',
            'controllers',
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
    public static function getBundleDependencies(KernelInterface $kernel) : array
    {
        return [
            PuntmigSearchBundle::class,
            FrameworkBundle::class,
            MonologBundle::class,
            TacticianBundle::class,
            DoctrineBundle::class,
        ];
    }

    /**
     * Return a CompilerPass instance array.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses() : array
    {
        return [
            new MappingCompilerPass(
                new MappingBagProvider()
            ),
        ];
    }
}
