<?php

/*
 * This file is part of the OneBundleApp package.
 *
 * Copyright (c) >=2017 Marc Morera
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

$environment = 'prod';
require __DIR__ . '/../vendor/autoload.php';
require 'FiniteServer.php';

\Symfony\Component\Debug\ErrorHandler::register();
\Symfony\Component\Debug\ExceptionHandler::register();

$oneBundleAppConfig = new \OneBundleApp\App\OneBundleAppConfig(__DIR__ . '/../', $environment);
$kernel = new \Mmoreram\BaseBundle\Kernel\BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    $oneBundleAppConfig->getRoutes(),
    $environment,
    false,
    realpath(__DIR__ . '/../var')
);

/**
 * REACT SERVER
 */
$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server($argv[1], $loop);
$limitedServer = new FiniteServer($socket, $argv[2]);


$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($kernel) {
    return new \React\Promise\Promise(function ($resolve, $reject) use ($request, $kernel) {

        $body = '';
        $request->getBody()->on('data', function ($data) use (&$body) {
            $body .= $data;
        });

        $request->getBody()->on('end', function () use ($resolve, &$body, $request, $kernel){
echo $request->getUri() . PHP_EOL;
            try {
                $method = $request->getMethod();
                $headers = $request->getHeaders();
                $query = $request->getQueryParams();
                $post = array();
                if (!empty($body)) {
                    parse_str($body, $post);
                    $post = is_array($post)
                        ? $post
                        : [];
                }

                $symfonyRequest = new \Symfony\Component\HttpFoundation\Request(
                    $query,
                    $post,
                    $request->getAttributes(),
                    $request->getCookieParams(),
                    $request->getUploadedFiles(),
                    array(), // Server is partially filled a few lines below
                    $body
                );

                $symfonyRequest->setMethod($method);
                $symfonyRequest->headers->replace($headers);
                $symfonyRequest->server->set('REQUEST_URI', $request->getUri());
                if (isset($headers['Host'])) {
                    $symfonyRequest->server->set('SERVER_NAME', explode(':', $headers['Host'][0]));
                }

                $decodedUrl = urldecode($symfonyRequest->getRequestUri());
                if ($decodedUrl != '/v1/ping') {
                    toSyslog(LOG_INFO, sprintf("::: [%s]",
                        $decodedUrl
                    ));
                }

                $symfonyResponse = $kernel->handle($symfonyRequest);
                $kernel->terminate($symfonyRequest, $symfonyResponse);
                $httpResponse = new \React\Http\Response(
                    $symfonyResponse->getStatusCode(),
                    $symfonyResponse->headers->all(),
                    $symfonyResponse->getContent()
                );

                /**
                 * Catching errors and sending to syslog
                 */
            } catch (\Exception $e) {

                exceptionToSyslog($e);
                throw $e;
            }

            $resolve($httpResponse);
        });

        $request->getBody()->on('error', function (Exception $e) use ($resolve){
            exceptionToSyslog($e);
            $response = new \React\Http\Response(
                400,
                array('Content-Type' => 'text/plain'),
                "An error occured while reading from stream"
            );
            $resolve($response);
        });
    });
});

$http->on('error', function(\Exception $e) {
    exceptionToSyslog($e);
});

$http->listen($limitedServer);
$loop->run();






/**
 * Common functions
 */

/**
 * Send to syslog
 *
 * @param \Exception $e
 */
function exceptionToSyslog(\Exception $e)
{
    openlog("search-server", LOG_PID, LOG_LOCAL0);
    syslog(LOG_ALERT, "[{$e->getFile()}] [{$e->getCode()}] ::: [{$e->getMessage()}]");
    closelog();
}

/**
 * Send to syslog
 *
 * @param string $level
 * @param string $content
 */
function toSyslog(
    string $level,
    string $content
) {
    openlog("search-server", LOG_PID, LOG_LOCAL0);
    syslog($level, $content);
    closelog();
}
