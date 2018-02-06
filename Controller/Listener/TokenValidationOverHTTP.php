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

namespace Apisearch\Server\Controller\Listener;

use Apisearch\Exception\InvalidTokenException;
use Apisearch\Server\Domain\Token\TokenValidator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class TokenValidationOverHTTP.
 */
class TokenValidationOverHTTP
{
    /**
     * @var TokenValidator
     *
     * Token validator
     */
    private $tokenValidator;

    /**
     * TokenValidationOverHTTP constructor.
     *
     * @param TokenValidator $tokenValidator
     */
    public function __construct(TokenValidator $tokenValidator)
    {
        $this->tokenValidator = $tokenValidator;
    }

    /**
     * Validate token given a Request.
     *
     * @param GetResponseEvent $event
     */
    public function validateTokenOnKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $query = $request->query;
        $token = $query->get('token');
        if (is_null($token)) {
            throw InvalidTokenException::createInvalidTokenPermissions('');
        }

        $origin = $request->headers->get('Origin', '');
        $origin = str_replace(['http://', 'https://'], ['', ''], $origin);

        $token = $this
            ->tokenValidator
            ->validateToken(
                $query->get('app_id', ''),
                $query->get('index_id', ''),
                $token,
                $origin,
                $request->getPathInfo(),
                $request->getMethod()
            );

        $request
            ->query
            ->set('token', $token);
    }
}
