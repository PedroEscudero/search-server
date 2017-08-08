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

namespace Puntmig\Search\Server\Tests\Functional\Repository;

/**
 * Class AServiceRepositoryTest.
 *
 * The "A" of the class name is because tests will test first of all direct
 * service repository instead of HTTP Test, that is faster. If any error occurs,
 * will be know much faster.
 */
class AServiceRepositoryTest extends RepositoryTest
{
    /**
     * get repository service name.
     *
     * @return string
     */
    protected static function getRepositoryServiceName() : string
    {
        return 'search_server.elastica_repository';
    }
}
