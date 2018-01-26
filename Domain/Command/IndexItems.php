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

namespace Apisearch\Server\Domain\Command;

use Apisearch\Model\Item;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Repository\WithTokenTrait;
use Apisearch\Server\Domain\LoggableCommand;
use Apisearch\Server\Domain\WriteCommand;
use Apisearch\Token\Token;

/**
 * Class IndexItems.
 */
class IndexItems implements WithRepositoryReference, WriteCommand, LoggableCommand
{
    use WithRepositoryReferenceTrait;
    use WithTokenTrait;

    /**
     * @var Item[]
     *
     * Items
     */
    private $items;

    /**
     * IndexCommand constructor.
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param Item[]              $items
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token              $token,
        array $items
    ) {
        $this->repositoryReference = $repositoryReference;
        $this->token = $token;
        $this->items = $items;
    }

    /**
     * Get Items.
     *
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
