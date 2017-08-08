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

namespace Puntmig\Search\Server\Domain\Middleware;

use League\Tactician\Middleware;

use Puntmig\Search\Server\Domain\Command\WithKeyCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;

/**
 * Class CheckKeyMiddleware.
 */
class CheckKeyMiddleware implements Middleware
{
    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     *
     * @throws InvalidKeyException
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof WithKeyCommand) {
            if (is_null($command->getKey())) {
                throw new InvalidKeyException();
            }
        }

        return $next($command);
    }
}
