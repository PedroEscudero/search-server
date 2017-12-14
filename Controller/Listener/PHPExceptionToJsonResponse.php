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

use Apisearch\Server\Domain\Exception\InvalidFormatException;
use Apisearch\Server\Domain\Exception\InvalidKeyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * File header placeholder.
 */
class PHPExceptionToJsonResponse
{
    /**
     * When controller gets exception.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof InvalidKeyException) {
            $event->setResponse(new JsonResponse([
                'message' => 'Invalid key',
            ], 401));

            return;
        }

        if ($exception instanceof InvalidFormatException) {
            $event->setResponse(new JsonResponse([
                'message' => 'Invalid format',
            ], 400));

            return;
        }
    }
}
