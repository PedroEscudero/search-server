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

namespace Apisearch\Server\Domain\Token;

use Apisearch\Exception\InvalidTokenException;
use Apisearch\Token\Token;
use Apisearch\Token\TokenUUID;
use Carbon\Carbon;

/**
 * Class TokenValidator.
 */
class TokenValidator
{
    /**
     * @var TokenLocator
     *
     * Token locator
     */
    private $tokenLocator;

    /**
     * @var string
     *
     * God token
     */
    private $godToken;

    /**
     * @var string
     *
     * Ping token
     */
    private $pingToken;

    /**
     * TokenValidator constructor.
     *
     * @param TokenLocator $tokenLocator
     * @param string       $godToken
     * @param string       $pingToken
     */
    public function __construct(
        TokenLocator $tokenLocator,
        string $godToken,
        string $pingToken
    ) {
        $this->tokenLocator = $tokenLocator;
        $this->godToken = $godToken;
        $this->pingToken = $pingToken;
    }

    /**
     * Validate token given basic fields.
     *
     * If is valid, return valid Token
     *
     * @param string $appId
     * @param string $indexId
     * @param string $tokenReference
     * @param string $referrer
     * @param string $path
     * @param string $verb
     *
     * @return Token $token
     */
    public function validateToken(
        string $appId,
        string $indexId,
        string $tokenReference,
        string $referrer,
        string $path,
        string $verb
    ): Token {
        if ($tokenReference === $this->godToken) {
            return $this->createGodToken($appId);
        }

        if ($tokenReference === $this->pingToken) {
            return $this->createPingToken();
        }

        $endpoint = strtolower($verb.'~~'.trim($path, '/'));
        $token = $this
            ->tokenLocator
            ->getTokenByReference(
                $appId,
                $tokenReference
            );

        if (
            (!$token instanceof Token) ||
            (
                $appId !== $token->getAppId()
            ) ||
            (
                !empty($token->getHttpReferrers()) &&
                !in_array($referrer, $token->getHttpReferrers())
            ) ||
            (
                !empty($token->getIndices()) &&
                !in_array($indexId, $token->getIndices())
            ) ||
            (
                !empty($token->getEndpoints()) &&
                !in_array($endpoint, $token->getEndpoints())
            ) ||
            (
                $token->getSecondsValid() > 0 &&
                $token->getUpdatedAt() + $token->getSecondsValid() < Carbon::now('UTC')->timestamp
            )
        ) {
            throw InvalidTokenException::createInvalidTokenPermissions($tokenReference);
        }

        return $token;
    }

    /**
     * Create god token instance.
     *
     * @param string $appId
     *
     * @return Token
     */
    private function createGodToken(string $appId): Token
    {
        return new Token(
            TokenUUID::createById($this->godToken),
            $appId
        );
    }

    /**
     * Create ping token instance.
     *
     * @return Token
     */
    private function createPingToken(): Token
    {
        return new Token(
            TokenUUID::createById($this->pingToken),
            ''
        );
    }
}
