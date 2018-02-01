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

use Apisearch\Token\Token;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class TokenCacheOverHTTP.
 */
class TokenCacheOverHTTP
{
    /**
     * Add cache control on kernel response
     *
     * @param FilterResponseEvent $event
     */
    public function addCacheControlOnKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $query = $request->query;
        $token = $query->get('token');

        if (
            $request->isMethod(Request::METHOD_GET) &&
            $token instanceof Token &&
            $token->getTtl() > 0
        ) {
            $response->setTtl($token->getTtl());
            $response->setClientTtl($token->getTtl());
            $response->setPublic();
        } else {
            $response->setTtl(0);
            $response->setClientTtl(0);
            $response->setPrivate();
        }
    }
}
