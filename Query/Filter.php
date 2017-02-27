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
     * Filter type field
     */
    const TYPE_FIELD = 'field';

    /**
     * @var string
     *
     * Filter type field
     */
    const TYPE_NESTED = 'nested';

    /**
     * @var string
     *
     * Filter type field
     */
    const TYPE_RANGE = 'range';

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
    private $applicationType;

    /**
     * @var string
     *
     * Filter type
     */
    private $filterType;

    /**
     * Filter constructor.
     *
     * @param string $field
     * @param array  $values
     * @param string $applicationType
     * @param string $filterType
     */
    private function __construct(
        string $field,
        array $values,
        string $applicationType,
        string $filterType
    ) {
        $this->field = $field;
        $this->values = $values;
        $this->applicationType = $applicationType;
        $this->filterType = $filterType;
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
     * Get application type.
     *
     * @return string
     */
    public function getApplicationType(): string
    {
        return $this->applicationType;
    }

    /**
     * Get filter type.
     *
     * @return string
     */
    public function getFilterType(): string
    {
        return $this->filterType;
    }

    /**
     * Create filter.
     *
     * @param string $field
     * @param array  $values
     * @param string $applicationType
     * @param string $filterType
     *
     * @return self
     */
    public static function create(
        string $field,
        array $values,
        string $applicationType,
        string $filterType
    ) : self {
        return new self(
            $field,
            $values,
            $applicationType,
            $filterType
        );
    }
}
