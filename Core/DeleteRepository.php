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

namespace Puntmig\Search\Server\Core;

use Puntmig\Search\Model\Brand;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\Tag;

/**
 * Class DeleteRepository.
 */
class DeleteRepository extends ElasticaWithKeyWrapper
{
    /**
     * Delete products.
     *
     * @param string[] $productIds
     */
    public function deleteProducts(array $productIds)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Product::TYPE
            )
            ->deleteIds($productIds);

        $this->refresh();
    }

    /**
     * Delete categories.
     *
     * @param string[] $categoryIds
     */
    public function deleteCategories(array $categoryIds)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Category::TYPE
            )
            ->deleteIds($categoryIds);

        $this->refresh();
    }

    /**
     * Delete manufacturers.
     *
     * @param string[] $manufacturerIds
     */
    public function deleteManufacturers(array $manufacturerIds)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Manufacturer::TYPE
            )
            ->deleteIds($manufacturerIds);

        $this->refresh();
    }

    /**
     * Delete brands.
     *
     * @param string[] $brandIds
     */
    public function deleteBrands(array $brandIds)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Brand::TYPE
            )
            ->deleteIds($brandIds);

        $this->refresh();
    }

    /**
     * Delete tags.
     *
     * @param string[] $tagIds
     */
    public function deleteTags(array $tagIds)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Tag::TYPE
            )
            ->deleteIds($tagIds);

        $this->refresh();
    }
}
