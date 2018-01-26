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

namespace Apisearch\Server\Domain\Event;

use Apisearch\User\Interaction;

/**
 * Class InteractionWasAdded.
 */
class InteractionWasAdded extends DomainEvent
{
    /**
     * @var Interaction
     *
     * Interaction
     */
    private $interaction;

    /**
     * ItemsWasIndexed constructor.
     */
    public function __construct(Interaction $interaction)
    {
        $this->setNow();
        $this->interaction = $interaction;
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function readableOnlyToArray(): array
    {
        return [
            'interaction' => $this
                ->interaction
                ->toArray(),
        ];
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function indexableToArray(): array
    {
        return [];
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
        return [
            Interaction::createFromArray(json_decode($data, true)['interaction']),
        ];
    }
}
