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

namespace Apisearch\Server\Console;

use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Command\Command;

/**
 * Class CommandWithBusAndGodToken.
 */
class CommandWithBusAndGodToken extends Command
{
    /**
     * @var CommandBus
     *
     * Message bus
     */
    protected $commandBus;

    /**
     * @var string
     *
     * God token
     */
    private $godToken;

    /**
     * Controller constructor.
     *
     * @param CommandBus $commandBus
     * @param string     $godToken
     */
    public function __construct(
        CommandBus $commandBus,
        string $godToken
    ) {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->godToken = $godToken;
    }

    /**
     * Create god token instance.
     *
     * @param string $appId
     *
     * @return Token
     */
    protected function createGodToken(string $appId): Token
    {
        return new Token(
            TokenUUID::createById($this->godToken),
            $appId
        );
    }
}
