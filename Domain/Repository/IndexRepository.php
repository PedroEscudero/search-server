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

namespace Puntmig\Search\Server\Domain\Repository;

use Puntmig\Search\Model\Item;

/**
 * Interface IndexRepository.
 */
interface IndexRepository extends RepositoryWithKey
{
    /**
     * Create the index.
     *
     * @param null|string $language
     */
    public function createIndex(? string $language);

    /**
     * Generate items documents.
     *
     * @param Item[] $items
     */
    public function addItems(array $items);
}
