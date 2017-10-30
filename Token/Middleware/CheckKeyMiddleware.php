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

namespace Puntmig\Search\Server\Token\Middleware;

use League\Tactician\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;
use Puntmig\Search\Server\Domain\WithAppIdAndKey;

/**
 * Class CheckKeyMiddleware.
 */
class CheckKeyMiddleware implements Middleware
{
    /**
     * @var RequestStack
     *
     * Request stack
     */
    private $requestStack;

    /**
     * CheckKeyMiddleware constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     *
     * @throws InvalidKeyException
     */
    public function execute($command, callable $next)
    {
        $currentRequest = $this
            ->requestStack
            ->getCurrentRequest();

        if (
            $command instanceof WithAppIdAndKey &&
            $currentRequest instanceof Request
        ) {
            if (
                is_null($command->getKey()) ||
                !$this->checkPermission(
                    $command,
                    $currentRequest
                )
            ) {
                throw new InvalidKeyException();
            }
        }

        return $next($command);
    }

    /**
     * Check permission.
     *
     * @param WithAppIdAndKey $command
     * @param Request         $request
     *
     * @return bool
     */
    public function checkPermission(
        WithAppIdAndKey $command,
        Request $request
    ): bool {
        $value = @file_get_contents('http://tokens.dev/permission?'.implode('&', [
            'project=search',
            'path='.$request->getPathInfo(),
            'verb='.$request->getRealMethod(),
            'app_id='.$command->getAppId(),
            'token='.$command->getKey(),
        ]));

        return false !== $value;
    }
}
