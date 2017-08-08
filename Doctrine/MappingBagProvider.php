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

namespace Puntmig\Search\Server\Doctrine;

use Mmoreram\BaseBundle\Mapping\MappingBagCollection;
use Mmoreram\BaseBundle\Mapping\MappingBagProvider as BaseMappingBagProvider;

/**
 * Class MappingBagProvider.
 */
class MappingBagProvider implements BaseMappingBagProvider
{
    /**
     * Get mapping bag collection.
     *
     * @return MappingBagCollection
     */
    public function getMappingBagCollection() : MappingBagCollection
    {
        return MappingBagCollection::create(
            [
                'event' => 'Event',
            ],
            '@PuntmigSearchServerBundle',
            'Puntmig\Search\Server\Domain\Event',
            '',
            'default',
            'object_manager',
            'object_repository',
            false
        );
    }
}
