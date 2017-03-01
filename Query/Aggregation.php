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
 * Class Aggregation.
 */
class Aggregation
{
    /**
     * @var string
     *
     * Name
     */
    private $name;

    /**
     * @var string
     *
     * Type
     */
    private $type;

    /**
     * @var string
     *
     * Field
     */
    private $field;

    /**
     * @var bool
     *
     * Is nested
     */
    private $nested;

    /**
     * Aggregation constructor.
     *
     * @param string $name
     * @param string $field
     * @param string $type
     * @param bool   $nested
     */
    public function __construct(
        string $name,
        string $field,
        string $type,
        bool $nested
    ) {
        $this->name = $name;
        $this->field = $field;
        $this->type = $type;
        $this->nested = $nested;
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
     * Get field.
     *
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Is nested.
     *
     * @return bool
     */
    public function isNested() : bool
    {
        return $this->nested;
    }

    /**
     * Create.
     *
     * @param string $name
     * @param string $field
     * @param string $type
     * @param bool   $nested
     *
     * @return self
     */
    public static function create(
        string $name,
        string $field,
        string $type,
        bool $nested
    ) : self {
        return new self(
            $name,
            $field,
            $type,
            $nested
        );
    }
}
