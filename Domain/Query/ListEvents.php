<?php

/*
 * This file is part of the Apisearch Server
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

namespace Apisearch\Server\Domain\Query;

use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;

/**
 * Class ListEvents.
 */
class ListEvents implements WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

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
     * @param RepositoryReference $repositoryReference
     * @param string|null         $name
     * @param int|null            $from
     * @param int|null            $to
     * @param int|null            $length
     * @param int|null            $offset
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        ?string $name,
        ?int $from,
        ?int $to,
        ?int $length,
        ?int $offset
    ) {
        $this->repositoryReference = $repositoryReference;
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
