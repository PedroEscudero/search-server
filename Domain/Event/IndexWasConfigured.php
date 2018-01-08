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

use Apisearch\Config\Config;

/**
 * Class IndexWasConfigured.
 */
class IndexWasConfigured extends DomainEvent
{
    /**
     * @var Config
     *
     * Config
     */
    private $config;

    /**
     * IndexWasConfigured constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->setNow();
    }

    /**
     * Indexable to array.
     *
     * @return array
     */
    public function readableOnlyToArray(): array
    {
        return [
            'config' => $this
                ->config
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
            Config::createFromArray($data['config']),
        ];
    }
}
