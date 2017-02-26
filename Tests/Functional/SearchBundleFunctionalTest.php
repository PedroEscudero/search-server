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

namespace Mmoreram\SearchBundle\Tests\Functional;

use Symfony\Component\HttpKernel\KernelInterface;

use Mmoreram\BaseBundle\Kernel\BaseKernel;
use Mmoreram\BaseBundle\Tests\BaseFunctionalTest;
use Mmoreram\SearchBundle\SearchBundle;

/**
 * Class SearchBundleFunctionalTest.
 */
abstract class SearchBundleFunctionalTest extends BaseFunctionalTest
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
                SearchBundle::class,
            ], [
                'imports' => [
                    ['resource' => '@BaseBundle/Resources/config/providers.yml'],
                    ['resource' => '@BaseBundle/Resources/test/framework.test.yml'],
                    ['resource' => '@BaseBundle/Resources/test/doctrine.test.yml'],
                ],
            ],
            [
                '@TagBundle/Resources/config/routing.yml',
            ],
            'test', true
        );
    }
}
