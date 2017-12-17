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

namespace Apisearch\Server\Domain\CommandHandler;

use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Apisearch\Server\Domain\WithEventRepository;

/**
 * Class CreateEventsIndexHandler.
 */
class CreateEventsIndexHandler extends WithEventRepository
{
    /**
     * Create the events index.
     *
     * @param CreateEventsIndex $createEventsIndex
     */
    public function handle(CreateEventsIndex $createEventsIndex)
    {
        $repositoryReference = $createEventsIndex->getRepositoryReference();

        $this
            ->eventRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->eventRepository
            ->createIndex(
                3,
                2
            );
    }
}
