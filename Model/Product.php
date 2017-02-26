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

namespace Mmoreram\SearchBundle\Model;

use DateTime;

/**
 * Class Product.
 */
class Product
{
    /**
     * @var string
     *
     * Id
     */
    private $id;

    /**
     * @var string
     *
     * family
     */
    private $family;

    /**
     * @var string
     *
     * EAN
     */
    private $ean;

    /**
     * @var string
     *
     * Name
     */
    private $name;

    /**
     * @var string
     *
     * Description
     */
    private $description;

    /**
     * @var string
     *
     * Long description
     */
    private $longDescription;

    /**
     * @var int
     *
     * price
     */
    private $price;

    /**
     * @var int
     *
     * Reduced price
     */
    private $reducedPrice;

    /**
     * @var Manufacturer
     *
     * Manufacturer
     */
    private $manufacturer;

    /**
     * @var Brand
     *
     * Brand
     */
    private $brand;

    /**
     * @var Category[]
     *
     * Categories
     */
    private $categories;

    /**
     * @var string
     *
     * Image
     */
    private $image;

    /**
     * @var string
     *
     * First level searchable data
     */
    private $firstLevelSearchableData;

    /**
     * @var string
     *
     * Second level searchable data
     */
    private $secondLevelSearchableData;

    /**
     * @var DateTime
     *
     * Updated at
     */
    private $updatedAt;

    /**
     * Product constructor.
     *
     * @param string       $id
     * @param string       $family
     * @param string       $ean
     * @param string       $name
     * @param string       $description
     * @param string       $longDescription
     * @param int          $price
     * @param int          $reducedPrice
     * @param Manufacturer $manufacturer
     * @param Brand        $brand
     * @param string       $image
     * @param DateTime     $updatedAt
     */
    public function __construct(
        string $id,
        string $family,
        string $ean,
        string $name,
        string $description,
        ? string $longDescription,
        int $price,
        ? int $reducedPrice,
        ? Manufacturer $manufacturer,
        ? Brand $brand,
        ? string $image,
        DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->family = $family;
        $this->ean = $ean;
        $this->name = $name;
        $this->description = $description;
        $this->longDescription = ($longDescription ?? '');
        $this->price = $price;
        $this->reducedPrice = ($reducedPrice ?? $price);
        $this->manufacturer = $manufacturer;
        $this->brand = $brand;
        $this->categories = [];
        $this->image = ($image ?? '');
        $this->updatedAt = ($updatedAt ?? new DateTime());

        $this->firstLevelSearchableData = "$name {$manufacturer->getName()} {$brand->getId()}";
        $this->secondLevelSearchableData = "$description $longDescription";
    }

    /**
     * Get product id.
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get family.
     *
     * @return string
     */
    public function getFamily() : string
    {
        return $this->family;
    }

    /**
     * Get EAN.
     *
     * @return string
     */
    public function getEan() : string
    {
        return $this->ean;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Get long description.
     *
     * @return string
     */
    public function getLongDescription(): string
    {
        return $this->longDescription;
    }

    /**
     * Get price.
     *
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * Get reduced price.
     *
     * @return int
     */
    public function getReducedPrice(): int
    {
        return $this->reducedPrice;
    }

    /**
     * Get real price.
     *
     * @return mixed
     */
    public function getRealPrice() : int
    {
        return min(
            $this->price,
            $this->reducedPrice
        );
    }

    /**
     * Get discount.
     *
     * @return int
     */
    public function getDiscount() : int
    {
        return $this->price - $this->getRealPrice();
    }

    /**
     * Get discount percentage.
     *
     * @return int
     */
    public function getDiscountPercentage() : int
    {
        return (int) round(100 * $this->getDiscount() / $this->getPrice());
    }

    /**
     * Get manufacturer.
     *
     * @return null|Manufacturer
     */
    public function getManufacturer() : ? Manufacturer
    {
        return $this->manufacturer;
    }

    /**
     * Get brand.
     *
     * @return null|Brand
     */
    public function getBrand() : ? Brand
    {
        return $this->brand;
    }

    /**
     * Add Category.
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        $this->firstLevelSearchableData .= ' ' . $category->getName();
    }

    /**
     * Get categories.
     *
     * @return Category[]
     */
    public function getCategories() : array
    {
        return $this->categories;
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Get Updated at.
     *
     * @return DateTime
     */
    public function getUpdatedAt() : DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Get first level searchable data.
     *
     * @return string
     */
    public function getFirstLevelSearchableData(): string
    {
        return $this->firstLevelSearchableData;
    }

    /**
     * Get second level searchable data.
     *
     * @return string
     */
    public function getSecondLevelSearchableData(): string
    {
        return $this->secondLevelSearchableData;
    }

    /**
     * Create from array.
     *
     * @param array $array
     *
     * @return Product
     */
    public static function createFromArray(array $array) : Product
    {
        $product = new self(
            (string) $array['id'],
            (string) $array['family'],
            (string) $array['ean'],
            (string) $array['name'],
            (string) $array['description'],
            $array['long_description'] ?? null,
            (int) $array['price'],
            $array['reduced_price'] ?? null,
            Manufacturer::createFromArray($array['manufacturer']),
            Brand::createFromArray($array['brand']),
            $array['image'] ?? null,
            $array['updated_at'] ?? null
        );

        if (
            isset($array['category']) &&
            is_array($array['category'])
        ) {
            foreach ($array['category'] as $category) {
                $product->addCategory(
                    Category::createFromArray($category)
                );
            }
        }

        return $product;
    }
}
