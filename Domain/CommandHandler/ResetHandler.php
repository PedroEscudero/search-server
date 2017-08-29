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

use Puntmig\Search\Server\Domain\Command\Reset as ResetCommand;
use Puntmig\Search\Server\Domain\Event\IndexWasReset;
use Puntmig\Search\Server\Domain\WithRepositoryAndEventPublisher;

/**
 * Class ResetHandler.
 */
class ResetHandler extends WithRepositoryAndEventPublisher
{
    /**
     * Reset the index.
     *
     * @param ResetCommand $resetCommand
     */
    public function handle(ResetCommand $resetCommand)
    {
        $key = $resetCommand->getKey();
        $language = $resetCommand->getLanguage();

        $this
            ->repository
            ->setKey($key);

        $this
            ->repository
            ->reset($language);

        $this
            ->eventPublisher
            ->publish(new IndexWasReset(
                $key,
                $language
            ));
    }
}
