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

/**
 * Class IdNameWrapper.
 */
abstract class IdNameWrapper
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
     * Name
     */
    private $name;

    /**
     * @var string
     *
     * First level searchable data
     */
    private $firstLevelSearchableData;

    /**
     * Category constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->firstLevelSearchableData = $name;
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get first level searchable data
     *
     * @return string
     */
    public function getFirstLevelSearchableData(): string
    {
        return $this->firstLevelSearchableData;
    }

    /**
     * Create from array.
     *
     * @param array $array
     *
     * @return static|self
     */
    public static function createFromArray(array $array) : ? self
    {
        if (
            !isset($array['id']) ||
            !isset($array['name'])
        ) {
            return null;
        }

        return new static(
            (string) $array['id'],
            (string) $array['name']
        );
    }
}
