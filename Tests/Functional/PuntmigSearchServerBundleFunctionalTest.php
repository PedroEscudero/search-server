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

use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Symfony\Component\HttpKernel\KernelInterface;

use Puntmig\Search\PuntmigSearchBundle;
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
        return false;
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
                PuntmigSearchServerBundle::class,
                PuntmigSearchBundle::class,
            ], [
                'imports' => [
                    ['resource' => '@PuntmigSearchServerBundle/Resources/test/http.yml'],
                ],
                'framework' => [
                    'test' => true,
                ],
            ],
            [
                '@PuntmigSearchServerBundle/Resources/config/routing.yml',
            ],
            'test', true
        );
    }
}
