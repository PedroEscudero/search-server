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

namespace Apisearch\Server\Socket;

use Apisearch\Server\Domain\Token\TokenValidator;
use Apisearch\Server\Redis\Token\TokenRedisRepository;
use RedisCluster;
use Symfony\Component\Yaml\Yaml;

/**
 * Class TokenValidatorFactory.
 */
class TokenValidatorFactory
{
    /**
     * Create token validator.
     *
     * @return TokenValidator
     */
    public static function create(): TokenValidator
    {
        $appConfig = Yaml::parse(file_get_contents(__DIR__.'/../app.yml'))['config'];
        $redis = new RedisCluster(null, ['127.0.0.1:'.$appConfig['rs_queue']['server']['redis']['port']]);

        return new TokenValidator(
            new TokenRedisRepository($redis),
            $appConfig['apisearch_server']['god_token'],
            $appConfig['apisearch_server']['ping_token']
        );
    }
}
