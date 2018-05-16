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
use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Apisearch\Server\Domain\Command\CreateIndex;
use Apisearch\Server\Domain\Command\CreateLogsIndex;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateIndexCommand.
 */
class CreateIndexCommand extends CommandWithBusAndGodToken
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:create-index')
            ->setDescription('Create an index')
            ->addOption(
                'app-id',
                'a',
                InputOption::VALUE_REQUIRED,
                'App id'
            )
            ->addOption(
                'index',
                'i',
                InputOption::VALUE_REQUIRED,
                'Index'
            )
            ->addOption(
                'with-events',
                null,
                InputOption::VALUE_OPTIONAL,
                'Create events as well'
            )
            ->addOption(
                'with-logs',
                null,
                InputOption::VALUE_OPTIONAL,
                'Create logs as well'
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
            ->handle(new CreateIndex(
                RepositoryReference::create(
                    $input->getOption('app-id'),
                    $input->getOption('index')
                ),
                $this->createGodToken($input->getOption('app-id'))
            ));

        if ($input->hasOption('with-events')) {
            $this->createEvents(
                $input->getOption('app-id'),
                $input->getOption('index')
            );
        }

        if ($input->hasOption('with-logs')) {
            $this->createLogs(
                $input->getOption('app-id'),
                $input->getOption('index')
            );
        }
    }

    /**
     * Create events index.
     *
     * @param string $appId
     * @param string $index
     */
    private function createEvents(
        string $appId,
        string $index
    ) {
        $this
            ->commandBus
            ->handle(new CreateEventsIndex(
                RepositoryReference::create(
                    $appId,
                    $index
                ),
                $this->createGodToken($appId)
            ));
    }

    /**
     * Create logs index.
     *
     * @param string $appId
     * @param string $index
     */
    private function createLogs(
        string $appId,
        string $index
    ) {
        $this
            ->commandBus
            ->handle(new CreateLogsIndex(
                RepositoryReference::create(
                    $appId,
                    $index
                ),
                $this->createGodToken($appId)
            ));
    }
}
