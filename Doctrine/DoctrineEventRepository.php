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

use Puntmig\Search\Server\Domain\Event\DomainEvent;
use Puntmig\Search\Server\Domain\Event\Event;
use Puntmig\Search\Server\Domain\Event\EventStore;

/**
 * Class DoctrineEventRepository.
 */
class DoctrineEventRepository extends EntityRepository implements EventStore
{
    /**
     * Append event.
     *
     * @param DomainEvent      $event
     * @param null|DomainEvent $previousEvent
     */
    public function append(
        DomainEvent $event,
        ? DomainEvent $previousEvent = null
    ) {
        $this->getEntityManager()->persist(new Event(
            $previousEvent ?? $this->findLastEvent(),
            get_class($event),
            $event->getKey(),
            $event->toPayload(),
            $event->occurredOn()
        ));
    }

    /**
     * Get last event.
     *
     * @return Event|null
     */
    private function findLastEvent() : ? Event
    {
        return $this
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->setFirstResult(0)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all events.
     *
     * @param int $length
     * @param int $offset
     *
     * @return DomainEvent[]
     */
    public function all(
        int $length = 10,
        int $offset = 0
    ) : array {
        $events = $this
            ->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($length)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        return array_map(function (Event $event) {
            $namespace = $event->getName();

            return $namespace::createByPlainValues(
                $event->getKey(),
                $event->getOccurredOn(),
                $event->getPayload()
            );
        }, $events);
    }
}
