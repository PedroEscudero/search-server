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

namespace Apisearch\Server\Domain\QueryHandler;

use Apisearch\Model\Item;
use Apisearch\Query\Filter;
use Apisearch\Query\Query as ApisearchQuery;
use Apisearch\Result\Result;
use Apisearch\Server\Domain\Event\QueryWasMade;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class QueryHandler.
 */
class QueryHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the query.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function handle(Query $query)
    {
        $searchQuery = $query->getQuery();

        $this
            ->repository
            ->setRepositoryReference($query->getRepositoryReference());

        $result = $this
            ->repository
            ->query($searchQuery);

        $this
            ->eventPublisher
            ->publish(new QueryWasMade(
                $searchQuery->getQueryText(),
                $this->filterFiltersByType($searchQuery->getFilters(), Filter::TYPE_FIELD),
                array_keys($searchQuery->getSortBy())[0],
                array_values($searchQuery->getSortBy())[0]['order'],
                $searchQuery->getSize(),
                array_map(function (Item $item) {
                    return $item->composeUUID();
                }, $result->getItems()),
                $searchQuery->getUser()
            ));

        return $result;
    }

    /**
     * Filter filters by type.
     *
     * @param Filter[] $filters
     * @param string   $filterType
     *
     * @return Filter[]
     */
    private function filterFiltersByType(
        array $filters,
        string $filterType
    ): array {
        return
            array_values(
                array_filter(
                    $filters,
                    function (Filter $filter) use ($filterType) {
                        return $filter->getFilterType() === $filterType;
                    }
                )
            );
    }

    /**
     * Query has the minimums requirements to be added as an event.
     *
     * Not used at the moment.
     *
     * * Query with more than 1 char
     * * Or a filter applied
     */
    private function queryShouldBeAddedAsEvent(ApisearchQuery $query)
    {
        return
            strlen($query->getQueryText()) > 1 ||
            count($query->getFilters()) > 1
        ;
    }
}
