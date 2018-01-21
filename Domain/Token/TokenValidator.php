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
        $appId = $query->get('app_id');
        $indexId = $query->get('index_id');
        $tokenReference = $query->get('token');
        $endpoint = strtolower($request->getMethod().'~~'.trim($request->getPathInfo(), '/'));
        $token = $this
            ->tokenLocator
            ->getTokenByReference(
                $appId,
                $tokenReference
            );

        if (
            (!$token instanceof Token) ||
            (
                !empty($token->getHttpReferrers()) &&
                !in_array($request->headers->get('referer'), $token->getHttpReferrers())
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
                $token->getUpdatedAt() + $token->getSecondsValid() >= Carbon::now('UTC')->timestamp
            )
        ) {
            throw InvalidTokenException::createInvalidTokenPermissions($tokenReference);
        }
    }
}
