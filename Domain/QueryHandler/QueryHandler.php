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

namespace Puntmig\Search\Server\Domain\QueryHandler;

use Puntmig\Search\Query\Filter;
use Puntmig\Search\Result\Result;
use Puntmig\Search\Server\Domain\Event\QueryWasMade;
use Puntmig\Search\Server\Domain\Query\Query;
use Puntmig\Search\Server\Domain\WithRepositoryAndEventPublisher;

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
        $key = $query->getKey();
        $searchQuery = $query->getQuery();

        $this
            ->repository
            ->setKey($key);

        $result = $this
            ->repository
            ->query($searchQuery);

        $this
            ->eventPublisher
            ->publish(new QueryWasMade(
                $key,
                $searchQuery->getQueryText(),
                $this->filterFiltersByType($searchQuery->getFilters(), Filter::TYPE_FIELD),
                array_keys($searchQuery->getSortBy())[0],
                array_values($searchQuery->getSortBy())[0]['order'],
                $searchQuery->getSize(),
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
}
