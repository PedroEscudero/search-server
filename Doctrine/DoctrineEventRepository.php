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

namespace Puntmig\Search\Server\Doctrine;

use Doctrine\ORM\EntityRepository;

use Puntmig\Search\Event\Event;
use Puntmig\Search\Event\EventRepository;
use Puntmig\Search\Server\Domain\Event\DomainEvent;

/**
 * Class DoctrineEventRepository.
 */
class DoctrineEventRepository extends EntityRepository implements EventRepository
{
    /**
     * Get all events.
     *
     * @param string|null $appId
     * @param string|null $key
     * @param string|null $name
     * @param int|null    $from
     * @param int|null    $to
     * @param int|null    $length
     * @param int|null    $offset
     *
     * @return DomainEvent[]
     */
    public function all(
        string $appId = null,
        string $key = null,
        string $name = null,
        ?int $from = null,
        ?int $to = null,
        ?int $length = 10,
        ?int $offset = 0
    ): array {
        $queryBuilder = $this
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->setMaxResults($length)
            ->setFirstResult($offset);

        if (!is_null($appId)) {
            $queryBuilder
                ->andWhere('e.appId = :app_id')
                ->setParameter('app_id', $appId);
        }

        if (!is_null($key)) {
            $queryBuilder
                ->andWhere('e.key = :key')
                ->setParameter('key', $key);
        }

        if (!is_null($name)) {
            $queryBuilder
                ->andWhere('e.name = :name')
                ->setParameter('name', $name);
        }

        if (!is_null($from)) {
            $queryBuilder
                ->andWhere('e.occurredOn >= :from')
                ->setParameter('from', $from);
        }

        if (!is_null($to)) {
            $queryBuilder
                ->andWhere('e.occurredOn < :to')
                ->setParameter('to', $to);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * Save event.
     *
     * @param Event $event
     */
    public function save(Event $event)
    {
        $this
            ->getEntityManager()
            ->persist($event);
    }

    /**
     * Get last event.
     *
     * @return Event|null
     */
    public function last(): ? Event
    {
        return $this
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->setFirstResult(0)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
