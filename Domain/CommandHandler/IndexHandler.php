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

namespace Puntmig\Search\Server\Domain\CommandHandler;

use Puntmig\Search\Server\Domain\Command\IndexCommand;
use Puntmig\Search\Server\Domain\Event\EventPublisher;
use Puntmig\Search\Server\Domain\Event\ItemsWereIndexed;
use Puntmig\Search\Server\Domain\Repository\IndexRepository;

/**
 * Class IndexHandler.
 */
class IndexHandler
{
    /**
     * @var IndexRepository
     *
     * Index repository
     */
    private $indexRepository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * IndexHandler constructor.
     *
     * @param IndexRepository $indexRepository
     * @param EventPublisher  $eventPublisher
     */
    public function __construct(
        IndexRepository $indexRepository,
        EventPublisher $eventPublisher
    ) {
        $this->indexRepository = $indexRepository;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * Reset the index.
     *
     * @param IndexCommand $indexCommand
     */
    public function handle(IndexCommand $indexCommand)
    {
        $key = $indexCommand->getKey();
        $items = $indexCommand->getItems();

        $this
            ->indexRepository
            ->setKey($key);

        $this
            ->indexRepository
            ->addItems($items);

        $this
            ->eventPublisher
            ->publish(new ItemsWereIndexed(
                $key,
                $items
            ));
    }
}
