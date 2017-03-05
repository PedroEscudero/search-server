<?php

/*
 * This file is part of the SearchBundle for Symfony2.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Mmoreram\SearchBundle\Http;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleClient.
 */
class GuzzleClient implements HttpClient
{
    /**
     * @var string
     *
     * Host
     */
    private $host;

    /**
     * GuzzleClient constructor.
     *
     * @param string $host
     */
    public function __construct(string $host)
    {
        $this->host = $host;
    }

    /**
     * Get a response given some parameters.
     * Return an array with the status code and the body.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return array
     */
    public function get(
        string $url,
        string $method,
        array $options
    ): array {
        $client = new Client([
            'defaults' => [
                'timeout' => 5,
            ],
        ]);

        /**
         * @var ResponseInterface $response
         */
        $response = $client->$method(
            $this->host . '/' . $url,
            $options
        );

        return [
            'code' => $response->getStatusCode(),
            'body' => json_decode($response->getBody(), true),
        ];
    }
}
