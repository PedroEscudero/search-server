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

use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\DeleteToken;
use Apisearch\Token\TokenUUID;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteTokenCommand.
 */
class DeleteTokenCommand extends CommandWithBusAndGodToken
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:delete-token')
            ->setDescription('Delete a token')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID'
            )
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'App id'
            );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->commandBus
            ->handle(new DeleteToken(
                RepositoryReference::create(
                    $input->getArgument('app-id'),
                    '~~~'
                ),
                $this->createGodToken($input->getArgument('app-id')),
                TokenUUID::createById($input->getArgument('uuid'))
            ));
    }
}
