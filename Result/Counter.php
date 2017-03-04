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

namespace Mmoreram\SearchBundle\Result;

/**
 * Class Counter.
 */
class Counter
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
     * @var null|string
     *
     * Level
     */
    private $level;

    /**
     * @var bool
     *
     * Used
     */
    private $used;

    /**
     * @var int
     *
     * N
     */
    private $n;

    /**
     * Counter constructor.
     *
     * @param string   $id
     * @param string   $name
     * @param null|int $level
     * @param bool     $used
     * @param int      $n
     */
    private function __construct(
        string $id,
        string $name,
        ? int $level,
        bool $used,
        int $n
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->used = $used;
        $this->n = $n;
    }

    /**
     * Get id.
     *
     * @return string
     */
    public function getId() : string
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
     * Get level.
     *
     * @return null|int
     */
    public function getLevel() : ? int
    {
        return $this->level;
    }

    /**
     * Is used.
     *
     * @return bool
     */
    public function isUsed() : bool
    {
        return $this->used;
    }

    /**
     * Get N.
     *
     * @return int
     */
    public function getN(): int
    {
        return $this->n;
    }

    /**
     * Create.
     *
     * @param string $name
     * @param int    $n
     * @param array  $activeElements
     *
     * @return self
     */
    public static function createByActiveElements(
        string $name,
        int $n,
        array $activeElements
    ) : self {
        $id = $name;
        $level = null;
        $splittedParts = explode('~~', $name);
        if (count($splittedParts) > 1) {
            $id = $splittedParts[0];
            $name = $splittedParts[1];
        }

        if (count($splittedParts) > 2) {
            $level = (int) $splittedParts[2];
        }

        return new self(
            $id,
            $name,
            $level,
            in_array($id, $activeElements),
            $n
        );
    }
}
