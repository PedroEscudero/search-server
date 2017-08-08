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

namespace Puntmig\Search\Server\Tests\Functional;

use Mmoreram\BaseBundle\BaseBundle;
use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

use Puntmig\Search\Server\PuntmigSearchServerBundle;

/**
 * Class SearchBundleFunctionalTest.
 */
abstract class PuntmigSearchServerBundleFunctionalTest extends BaseFunctionalTest
{
    /**
     * Schema must be loaded in all test cases.
     *
     * @return bool
     */
    protected static function loadSchema() : bool
    {
        return true;
    }

    /**
     * Get kernel.
     *
     * @return KernelInterface
     */
    protected static function getKernel() : KernelInterface
    {
        return new BaseKernel(
            [
                BaseBundle::class,
                PuntmigSearchServerBundle::class,
            ], [
                'imports' => [
                    ['resource' => '@BaseBundle/Resources/config/providers.yml'],
                    ['resource' => '@PuntmigSearchServerBundle/Resources/config/doctrine.test.yml'],
                    ['resource' => '@PuntmigSearchServerBundle/Resources/config/tactician.yml'],
                    ['resource' => '@PuntmigSearchServerBundle/Resources/config/test.yml'],
                ],
                'framework' => [
                    'test' => true,
                ],
                'puntmig_search' => [
                    'repositories' => [
                        'search' => [
                            'endpoint' => 'xxx',
                            'secret' => 'hjk45hj4k4',
                            'repository_service' => static::getRepositoryServiceName(),
                            'test' => true,
                        ],
                    ],
                ],

            ],
            [
                '@PuntmigSearchServerBundle/Resources/config/routing.yml',
            ],
            'test', true
        );
    }

    /**
     * get repository service name.
     *
     * @return string
     */
    abstract protected static function getRepositoryServiceName() : string;
}
