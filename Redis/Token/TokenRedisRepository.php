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

namespace Apisearch\Server\Redis\Token;

use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Server\Domain\Repository\AppRepository\TokenRepository;
use Apisearch\Server\Domain\Token\TokenLocator;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use Redis;
use RedisCluster;

/**
 * Class TokenRedisRepository.
 */
class TokenRedisRepository implements TokenRepository, TokenLocator, WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * Redis hast id.
     *
     * @var string
     */
    const REDIS_KEY = 'apisearch_tokens';

    /**
     * @var Redis|RedisCluster
     *
     * redis client
     */
    private $redisClient;

    /**
     * TokenRedisRepository constructor.
     *
     * @param Redis|RedisCluster $redisClient
     */
    public function __construct($redisClient)
    {
        $this->redisClient = $redisClient;
    }

    /**
     * Get composed redis key.
     *
     * @param string $appId
     *
     * @return string
     */
    private function composeRedisKey(string $appId): string
    {
        return $appId.'~~'.self::REDIS_KEY;
    }

    /**
     * Add token.
     *
     * @param Token $token
     */
    public function addToken(Token $token)
    {
        $this
            ->redisClient
            ->hSet(
                $this->composeRedisKey($this->getAppId()),
                $token->getTokenUUID()->composeUUID(),
                json_encode($token->toArray())
            );
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     */
    public function deleteToken(TokenUUID $tokenUUID)
    {
        $this
            ->redisClient
            ->hDel(
                $this->composeRedisKey($this->getAppId()),
                $tokenUUID->composeUUID()
            );
    }

    /**
     * Get token by reference.
     *
     * @param string $appId
     * @param string $tokenReference
     *
     * @return null|Token
     */
    public function getTokenByReference(
        string $appId,
        string $tokenReference
    ): ? Token {
        $token = $this
            ->redisClient
            ->hGet(
                $this->composeRedisKey($appId),
                $tokenReference
            );

        return false === $token
            ? null
            : Token::createFromArray(json_decode($token, true));
    }
}
