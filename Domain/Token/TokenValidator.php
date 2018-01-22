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
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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
     * TokenValidator constructor.
     *
     * @param TokenLocator $tokenLocator
     */
    public function __construct(TokenLocator $tokenLocator)
    {
        $this->tokenLocator = $tokenLocator;
    }

    /**
     * Validate token given a Request.
     *
     * @param GetResponseEvent $event
     */
    public function validateTokenOnKernelRequest(GetResponseEvent $event)
    {
        return;
        
        $request = $event->getRequest();
        $query = $request->query;

        self::validateToken(
            $query->get('app_id'),
            $query->get('index_id', ''),
            $query->get('token'),
            $request->headers->get('Origin', ''),
            $request->getPathInfo(),
            $request->getMethod()
        );
    }

    /**
     * Validate token given basic fields.
     *
     * @param string $appId
     * @param string $indexId
     * @param string $tokenReference
     * @param string $referrer
     * @param string $path
     * @param string $verb
     */
    public function validateToken(
        string $appId,
        string $indexId,
        string $tokenReference,
        string $referrer,
        string $path,
        string $verb
    ) {
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
    }
}
