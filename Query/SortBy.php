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

namespace Mmoreram\SearchBundle\Query;

/**
 * Class SortBy.
 */
class SortBy
{
    /**
     * @var string
     *
     * Sort by score
     */
    const SCORE = '_score';

    /**
     * @var string
     *
     * Sort by price ASC
     */
    const PRICE_ASC = ['real_price' => 'asc'];

    /**
     * @var string
     *
     * Sort by price DESC
     */
    const PRICE_DESC = ['real_price' => 'desc'];

    /**
     * @var string
     *
     * Sort by discount ASC
     */
    const DISCOUNT_ASC = ['discount' => 'asc'];

    /**
     * @var string
     *
     * Sort by discount DESC
     */
    const DISCOUNT_DESC = ['discount' => 'desc'];

    /**
     * @var string
     *
     * Sort by discount percentage ASC
     */
    const DISCOUNT_PERCENTAGE_ASC = ['discount_percentage' => 'asc'];

    /**
     * @var string
     *
     * Sort by discount percentage DESC
     */
    const DISCOUNT_PERCENTAGE_DESC = ['discount_percentage' => 'desc'];

    /**
     * @var string
     *
     * Sort by update at ASC
     */
    const UPDATED_AT_ASC = ['updated_at' => 'asc'];

    /**
     * @var string
     *
     * Sort by update at ASC
     */
    const UPDATED_AT_DESC = ['updated_at' => 'desc'];

    /**
     * @var string
     *
     * Sort by manufacturer ASC
     */
    const MANUFACTURER_ASC = ['manufacturer.sortable_name' => 'asc'];

    /**
     * @var string
     *
     * Sort by manufacturer DESC
     */
    const MANUFACTURER_DESC = ['manufacturer.sortable_name' => 'desc'];

    /**
     * @var string
     *
     * Sort by manufacturer ASC
     */
    const BRAND_ASC = ['brand.sortable_name' => 'asc'];

    /**
     * @var string
     *
     * Sort by manufacturer DESC
     */
    const BRAND_DESC = ['brand.sortable_name' => 'desc'];

    /**
     * @var array|string
     *
     * Sort value
     */
    private $value;

    /**
     * SortBy constructor.
     *
     * @param array|string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return array|string
     */
    public function getValue()
    {
        return $this->value;
    }
}
