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
 * Class Filter.
 */
class Filter
{
    /**
     * @var string
     *
     * Accumulative
     */
    const AT_LEAST_ONE = 'at_least_one';

    /**
     * @var string
     *
     * Filter
     */
    const MUST_ALL = 'must_all';

    /**
     * @var string
     *
     * Field
     */
    private $field;

    /**
     * @var array
     *
     * Values
     */
    private $values;

    /**
     * @var string
     *
     * Type
     */
    private $type;

    /**
     * @var bool
     *
     * Is nested
     */
    private $nested;

    /**
     * Filter constructor.
     *
     * @param string $field
     * @param array  $values
     * @param string $type
     * @param bool   $nested
     */
    private function __construct(
        string $field,
        array $values,
        string $type,
        bool $nested
    ) {
        $this->field = $field;
        $this->values = $values;
        $this->type = $type;
        $this->nested = $nested;
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
     * Get values.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
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
    public function isNested(): bool
    {
        return $this->nested;
    }

    /**
     * Create filter.
     *
     * @param string $field
     * @param array  $values
     * @param string $type
     * @param bool   $nested
     *
     * @return self
     */
    public static function create(
        string $field,
        array $values,
        string $type,
        bool $nested
    ) : self {
        return new self(
            $field,
            $values,
            $type,
            $nested
        );
    }
}
