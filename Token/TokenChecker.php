<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Token;

use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;

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
     * @throws InvalidKeyException
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
            throw InvalidKeyException::create();
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
