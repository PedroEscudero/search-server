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

namespace Puntmig\Search\Server\Domain\Exception;

use Exception;

/**
 * Class InvalidKeyException.
 */
class InvalidKeyException extends Exception
{
    /**
     * Throw an invalid key exception.
     *
     * @return InvalidKeyException
     */
    public static function create(): InvalidKeyException
    {
        return new self('Wrong API key provided');
    }
}
