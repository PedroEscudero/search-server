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

namespace Puntmig\Search\Server\Domain\Event;

use Puntmig\Search\Query\Filter;
use Puntmig\Search\Query\User;

/**
 * Class QueryWasMade.
 */
class QueryWasMade extends DomainEvent
{
    /**
     * @var string
     *
     * Query text
     */
    private $queryText;

    /**
     * @var Filter[]
     *
     * Applied filters
     */
    private $appliedFilters;

    /**
     * @var string
     *
     * Sort field
     */
    private $sortField;

    /**
     * @var string
     *
     * Sort direction
     */
    private $sortDirection;

    /**
     * @var int
     *
     * Size
     */
    private $size;

    /**
     * @var User|null
     *
     * User
     */
    private $user;

    /**
     * QueryWasMade constructor.
     *
     * @param string    $queryText
     * @param Filter[]  $appliedFilters
     * @param string    $sortField
     * @param string    $sortDirection
     * @param int       $size
     * @param User|null $user
     */
    public function __construct(
        string $queryText,
        array $appliedFilters,
        string $sortField,
        string $sortDirection,
        int $size,
        ? User $user
    ) {
        $this->queryText = $queryText;
        $this->appliedFilters = $appliedFilters;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->size = $size;
        $this->user = $user;
        $this->setNow();
    }

    /**
     * Payload to array.
     *
     * @return array
     */
    public function payloadToArray(): array
    {
        return array_filter([
            'q' => $this->queryText,
            'filters' => array_map(function (Filter $filter) {
                return $filter->toArray();
            }, $this->appliedFilters),
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection,
            'size' => $this->size,
            'user' => ($this->user instanceof User)
                ? $this->user->toArray()
                : null,
        ], function ($element) {
            return
            !(
                is_null($element) ||
                (is_array($element) && empty($element))
            );
        });
    }

    /**
     * To payload.
     *
     * @param string $data
     *
     * @return array
     */
    public static function stringToPayload(string $data): array
    {
        $payload = json_decode($data, true);

        return [
            $payload['q'],
            array_values(
                array_map(function (array $filter) {
                    return Filter::createFromArray($filter);
                }, ($payload['filters'] ?? []))
            ),
            $payload['sort_field'],
            $payload['sort_direction'],
            $payload['size'],
            isset($payload['user'])
                ? User::createFromArray($payload['user'])
                : null,
        ];
    }
}
