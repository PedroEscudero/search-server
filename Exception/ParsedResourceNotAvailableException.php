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

namespace Apisearch\Server\Exception;

use Apisearch\Exception\ResourceNotAvailableException;

/**
 * Class ResourceNotAvailableException.
 */
class ParsedResourceNotAvailableException
{
    /**
     * Index is not available.
     *
     * @param string $message
     *
     * @return ResourceNotAvailableException
     */
    public static function parsedIndexNotAvailable(string $message): ResourceNotAvailableException
    {
        return ResourceNotAvailableException::indexNotAvailable(self::transformToHumanFormat($message));
    }

    /**
     * Parse message and transform to a more human format.
     *
     * @param string $message
     *
     * @return string
     */
    private static function transformToHumanFormat(string $message): string
    {
        if (1 === preg_match(
            '#/apisearch_(?P<app_id>.*?)_(?P<index_id>.*?)/item/(?P<id>.*?)~(?P<type>.*?)caused failed to parse \[(?P<group>\w*?)\.(?P<field>\w*?)\]#i',
            $message,
            $match)) {
            return sprintf('Error while indexing item [id: %s, type: %s]. Field %s in %s is malformed',
                $match['id'],
                $match['type'],
                $match['field'],
                $match['group']
            );
        }

        return $message;
    }
}
