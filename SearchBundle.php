<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;

use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\SimpleBaseBundle;
use Mmoreram\SearchBundle\Command\GenerateProductsCommand;

/**
 * Class SearchBundle.
 */
class SearchBundle extends SimpleBaseBundle
{
    /**
     * get config files.
     *
     * @return array
     */
    public function getConfigFiles() : array
    {
        return [
            'repositories',
            'controllers',
            'elastica',
            'query',
            'twig',
            'http',
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
        $dependencies = [
            DoctrineBundle::class,
            FrameworkBundle::class,
            BaseBundle::class,
            TwigBundle::class,
        ];

        return $dependencies;
    }

    /**
     * Get command instance array.
     *
     * @return Command[]
     */
    public function getCommands() : array
    {
        return [
            new GenerateProductsCommand(),
        ];
    }
}
