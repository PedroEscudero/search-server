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

use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Domain\Command\QueryCommand;
use Puntmig\Search\Server\Domain\Event\EventPublisher;
use Puntmig\Search\Server\Domain\Event\QueryWasMade;
use Puntmig\Search\Server\Domain\Repository\QueryRepository;

/**
 * Class QueryHandler.
 */
class QueryHandler
{
    /**
     * @var QueryRepository
     *
     * Query repository
     */
    private $queryRepository;

    /**
     * @var EventPublisher
     *
     * Event publisher
     */
    private $eventPublisher;

    /**
     * QueryHandler constructor.
     *
     * @param QueryRepository $queryRepository
     * @param EventPublisher  $eventPublisher
     */
    public function __construct(
        QueryRepository $queryRepository,
        EventPublisher $eventPublisher
    ) {
        $this->queryRepository = $queryRepository;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * Reset the query.
     *
     * @param QueryCommand $queryCommand
     *
     * @return Result
     */
    public function handle(QueryCommand $queryCommand)
    {
        $key = $queryCommand->getKey();
        $query = $queryCommand->getQuery();

        $this
            ->queryRepository
            ->setKey($key);

        $result = $this
            ->queryRepository
            ->query($query);

        $this
            ->eventPublisher
            ->publish(new QueryWasMade(
                $key,
                $result
            ));

        return $result;
    }
}
