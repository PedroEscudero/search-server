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

namespace Puntmig\Search\Server\Formatter;

use Monolog\Formatter\NormalizerFormatter;

/**
 * Class LogFormatter.
 */
class LogFormatter extends NormalizerFormatter
{
    /**
     * @param array $record
     */
    public function format(array $record)
    {
        parse_str(parse_url($record['context']['request_uri'])['query'], $query);

        return "Key - {$query['key']}" . PHP_EOL . 'Query - ' . print_r(json_decode($query['query'], true), true);
    }

    /**
     * @param array $records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }
}
