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

namespace Puntmig\Search\Server\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Puntmig\Search\Server\Domain\Command\Reset as ResetCommand;
use Puntmig\Search\Server\Domain\Exception\InvalidKeyException;

/**
 * Class ResetController.
 */
class ResetController extends Controller
{
    /**
     * Reset the index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidKeyException
     */
    public function reset(Request $request)
    {
        $this->checkToken(
            $request,
            $request->get('app_id', ''),
            $request->get('key', '')
        );

        $this
            ->commandBus
            ->handle(new ResetCommand(
                $request->get('app_id', ''),
                $request->get('language', null)
            ));

        return new JsonResponse('Index created', 200);
    }
}
