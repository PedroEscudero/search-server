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

namespace Puntmig\Search\Server\Domain\Query;

use Puntmig\Search\Server\Domain\WithAppIdAndKey;

/**
 * Class ListEvents.
 */
class ListEvents extends WithAppIdAndKey
{
    /**
     * @var string|null
     *
     * Name
     */
    private $name;

    /**
     * @var int|null
     *
     * From
     */
    private $from;

    /**
     * @var int|null
     *
     * To
     */
    private $to;

    /**
     * @var int
     *
     * Length
     */
    private $length;

    /**
     * @var int
     *
     * Offset
     */
    private $offset;

    /**
     * DeleteCommand constructor.
     *
     * @param string      $appId
     * @param string      $key
     * @param string|null $name
     * @param int|null    $from
     * @param int|null    $to
     * @param int|null    $length
     * @param int|null    $offset
     */
    public function __construct(
        string $appId,
        string $key,
        ?string $name,
        ?int $from,
        ?int $to,
        ?int $length,
        ?int $offset
    ) {
        $this->appId = $appId;
        $this->key = $key;
        $this->name = $name;
        $this->from = $from;
        $this->to = $to;
        $this->length = $length;
        $this->offset = $offset;
    }

    /**
     * Get Name.
     *
     * @return null|string
     */
    public function getName(): ? string
    {
        return $this->name;
    }

    /**
     * Get From.
     *
     * @return int|null
     */
    public function getFrom(): ? int
    {
        return $this->from;
    }

    /**
     * Get To.
     *
     * @return int|null
     */
    public function getTo(): ? int
    {
        return $this->to;
    }

    /**
     * Get Length.
     *
     * @return null|int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * Get Offset.
     *
     * @return null|int
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }
}
