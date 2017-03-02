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
     * @var int
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
     * @var string[]
     *
     * Subgroup
     */
    private $subgroup;

    /**
     * Aggregation constructor.
     *
     * @param string $name
     * @param string $field
     * @param int    $type
     * @param bool   $nested
     * @param array  $subgroup
     */
    private function __construct(
        string $name,
        string $field,
        int $type,
        bool $nested,
        array $subgroup
    ) {
        $this->name = $name;
        $this->field = $field;
        $this->type = $type;
        $this->nested = $nested;
        $this->subgroup = $subgroup;
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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Get subgroup.
     *
     * @return string[]
     */
    public function getSubgroup(): array
    {
        return $this->subgroup;
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
     * @param int    $type
     * @param bool   $nested
     * @param array  $subgroup
     *
     * @return self
     */
    public static function create(
        string $name,
        string $field,
        int $type,
        bool $nested,
        array $subgroup = []
    ) : self {
        return new self(
            $name,
            $field,
            $type,
            $nested,
            $subgroup
        );
    }
}
