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

namespace Puntmig\Search\Server\Domain\Command;

/**
 * Abstract class WithKeyCommand.
 */
abstract class WithKeyCommand
{
    /**
     * @var string
     *
     * Key
     */
    protected $key;

    /**
     * Get Key.
     *
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }
}
