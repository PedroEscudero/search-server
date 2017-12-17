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

namespace Apisearch\Server\Token;

use Apisearch\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TokenChecker.
 */
class TokenChecker
{
    /**
     * @var string
     *
     * Token endpoint
     */
    private $tokenServerEndpoint;

    /**
     * CheckKeyMiddleware constructor.
     *
     * @param string $tokenServerEndpoint
     */
    public function __construct(string $tokenServerEndpoint)
    {
        $this->tokenServerEndpoint = $tokenServerEndpoint;
    }

    /**
     * Check tocket validity.
     *
     * @param Request $request
     * @param string  $appId
     * @param string  $token
     *
     * @throws InvalidTokenException
     */
    public function checkToken(
        Request $request,
        string $appId,
        string $token
    ) {
        if (
            is_null($token) ||
            !$this->checkPermission(
                $request,
                $appId,
                $token
            )
        ) {
            throw InvalidTokenException::create();
        }
    }

    /**
     * Check permission.
     *
     * @param Request $request
     * @param string  $appId
     * @param string  $token
     *
     * @return bool
     */
    public function checkPermission(
        Request $request,
        string $appId,
        string $token
    ): bool {
        $value = @file_get_contents($this->tokenServerEndpoint.'/permission?'.implode('&', [
            'project=search',
            'path='.$request->getPathInfo(),
            'verb='.$request->getRealMethod(),
            'app_id='.$appId,
            'token='.$token,
        ]));

        return false !== $value;
    }
}
