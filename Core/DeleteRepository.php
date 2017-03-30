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
use Puntmig\Search\Model\BrandReference;
use Puntmig\Search\Model\Category;
use Puntmig\Search\Model\CategoryReference;
use Puntmig\Search\Model\Manufacturer;
use Puntmig\Search\Model\ManufacturerReference;
use Puntmig\Search\Model\Product;
use Puntmig\Search\Model\ProductReference;
use Puntmig\Search\Model\Tag;
use Puntmig\Search\Model\TagReference;

/**
 * Class DeleteRepository.
 */
class DeleteRepository extends ElasticaWithKeyWrapper
{
    /**
     * Delete products.
     *
     * @param ProductReference[] $productReferences
     */
    public function deleteProducts(array $productReferences)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Product::TYPE
            )
            ->deleteIds(
                array_map(function (ProductReference $productReference) {
                    return $productReference->composeUUID();
                }, $productReferences)
            );

        $this->refresh();
    }

    /**
     * Delete categories.
     *
     * @param CategoryReference[] $categoryReferences
     */
    public function deleteCategories(array $categoryReferences)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Category::TYPE
            )
            ->deleteIds(
                array_map(function (CategoryReference $categoryReference) {
                    return $categoryReference->composeUUID();
                }, $categoryReferences)
            );

        $this->refresh();
    }

    /**
     * Delete manufacturers.
     *
     * @param ManufacturerReference[] $manufacturerReferences
     */
    public function deleteManufacturers(array $manufacturerReferences)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Manufacturer::TYPE
            )
            ->deleteIds(
                array_map(function (ManufacturerReference $manufacturerReference) {
                    return $manufacturerReference->composeUUID();
                }, $manufacturerReferences)
            );

        $this->refresh();
    }

    /**
     * Delete brands.
     *
     * @param BrandReference[] $brandReferences
     */
    public function deleteBrands(array $brandReferences)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Brand::TYPE
            )
            ->deleteIds(
                array_map(function (BrandReference $brandReference) {
                    return $brandReference->composeUUID();
                }, $brandReferences)
            );

        $this->refresh();
    }

    /**
     * Delete tags.
     *
     * @param TagReference[] $tagReferences
     */
    public function deleteTags(array $tagReferences)
    {
        $this
            ->elasticaWrapper
            ->getType(
                $this->key,
                Tag::TYPE
            )
            ->deleteIds(
                array_map(function (TagReference $tagReference) {
                    return $tagReference->composeUUID();
                }, $tagReferences)
            );

        $this->refresh();
    }
}
