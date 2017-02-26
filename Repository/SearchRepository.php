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

namespace Mmoreram\SearchBundle\Repository;

use Mmoreram\SearchBundle\Model\Product;
use Mmoreram\SearchBundle\Model\Result;
use Mmoreram\SearchBundle\Query\Query;

/**
 * Search repository.
 */
interface SearchRepository
{
    /**
     * Index product.
     *
     * @param string  $user
     * @param Product $product
     */
    public function index(
        string $user,
        Product $product
    );

    /**
     * Search cross the index types.
     *
     * @param string $user
     * @param Query  $query
     *
     * @return Result
     */
    public function search(
        string $user,
        Query $query
    ) : Result;
}
