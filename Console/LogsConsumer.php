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

use Apisearch\Log\Log;
use Apisearch\Log\LogRepository;
use Apisearch\Repository\RepositoryReference;
use RSQueue\Command\ConsumerCommand;
use RSQueue\Services\Consumer;
use RSQueue\Services\Publisher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LogsConsumer.
 */
class LogsConsumer extends ConsumerCommand
{
    /**
     * @var LogRepository
     *
     * Log Repository
     */
    private $logRepository;

    /**
     * @var Publisher
     *
     * Publisher
     */
    private $publisher;

    /**
     * ConsumerCommand constructor.
     *
     * @param Consumer      $consumer
     * @param LogRepository $logRepository
     * @param Publisher     $publisher
     */
    public function __construct(
        Consumer $consumer,
        LogRepository $logRepository,
        Publisher $publisher
    ) {
        parent::__construct($consumer);

        $this->logRepository = $logRepository;
        $this->publisher = $publisher;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('apisearch-server:logs-consumer');
    }

    /**
     * Definition method.
     *
     * All RSQueue commands must implements its own define() method
     * This method will subscribe command to desired queues
     * with their respective methods
     */
    public function define()
    {
        $this->addQueue('apisearch:server:logs', 'persistLog');
    }

    /**
     * Persist domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $data
     */
    protected function persistLog(
        InputInterface $input,
        OutputInterface $output,
        array $data
    ) {
        $this
            ->logRepository
            ->setRepositoryReference(
                RepositoryReference::create(
                    $data['app_id'],
                    $data['index_id']
                )
            );

        $log = Log::createFromArray($data['log']);
        var_dump($log);
        $this
            ->logRepository
            ->save($log);

        $this->publishLog(
            $data['app_id'],
            $data['index_id'],
            $log
        );
    }

    /**
     * Publish the event into the regular events queue.
     *
     * @param string $appId
     * @param string $indexId
     * @param Log    $log
     */
    private function publishLog(
        string $appId,
        string $indexId,
        Log $log
    ) {
        $this
            ->publisher
            ->publish('apisearch:logs', [
                'app_id' => $appId,
                'index_id' => $indexId,
                'log' => $log->toArray(),
            ]);
    }
}
