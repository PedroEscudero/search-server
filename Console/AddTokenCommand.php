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
use Apisearch\Server\Domain\Command\AddToken;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddTokenCommand.
 */
class AddTokenCommand extends CommandWithBusAndGodToken
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apisearch:add-token')
            ->setDescription('Add a token')
            ->addArgument(
                'uuid',
                InputArgument::REQUIRED,
                'UUID'
            )
            ->addArgument(
                'app-id',
                InputArgument::REQUIRED,
                'App id'
            )
            ->addOption(
                'index',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Indices',
                []
            )
            ->addOption(
                'http-referrer',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Http referrers',
                []
            )
            ->addOption(
                'endpoint',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Endpoints',
                []
            )
            ->addOption(
                'plugin',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Plugins',
                []
            )
            ->addOption(
                'seconds-valid',
                null,
                InputOption::VALUE_OPTIONAL,
                'Seconds valid',
                Token::INFINITE_DURATION
            )
            ->addOption(
                'max-hits-per-query',
                null,
                InputOption::VALUE_OPTIONAL,
                'Maximum hits per query',
                Token::INFINITE_HITS_PER_QUERY
            )
            ->addOption(
                'ttl',
                null,
                InputOption::VALUE_OPTIONAL,
                'TTL',
                Token::DEFAULT_TTL
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
            ->handle(new AddToken(
                RepositoryReference::create(
                    $input->getArgument('app-id'),
                    '~~~'
                ),
                $this->createGodToken($input->getArgument('app-id')),
                new Token(
                    TokenUUID::createById($input->getArgument('uuid')),
                    (string) $input->getArgument('app-id'),
                    $input->getOption('index'),
                    $input->getOption('http-referrer'),
                    $input->getOption('endpoint'),
                    $input->getOption('plugin'),
                    (int) $input->getOption('seconds-valid'),
                    (int) $input->getOption('max-hits-per-query'),
                    (int) $input->getOption('ttl')
                )
            ));
    }
}
