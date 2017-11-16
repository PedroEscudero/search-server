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
$limitedServer = new LimitingServer($socket, $argv[2]);
$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($kernel) {

    try {
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $query = $request->getQueryParams();
        $content = $request->getBody();
        $post = array();
        if (in_array(strtoupper($method), array('POST', 'PUT', 'DELETE', 'PATCH')) &&
            isset($headers['Content-Type']) && (0 === strpos($headers['Content-Type'], 'application/x-www-form-urlencoded'))
        ) {
            parse_str($content, $post);
        }
        $symfonyRequest = new \Symfony\Component\HttpFoundation\Request(
            $query,
            $post,
            $request->getAttributes(),
            $request->getCookieParams(),
            $request->getUploadedFiles(),
            array(), // Server is partially filled a few lines below
            $content
        );

        $symfonyRequest->setMethod($method);
        $symfonyRequest->headers->replace($headers);
        $symfonyRequest->server->set('REQUEST_URI', $request->getUri());
        if (isset($headers['Host'])) {
            $symfonyRequest->server->set('SERVER_NAME', explode(':', $headers['Host'][0]));
        }

        openlog("search-server", LOG_PID, LOG_LOCAL0);
        syslog(LOG_INFO, sprintf("[%s] ::: [%s]",
            $symfonyRequest->getClientIp(),
            $symfonyRequest->getRequestUri()
        ));
        closelog();

        $symfonyResponse = $kernel->handle($symfonyRequest);
        $httpResponse = new \React\Http\Response(
            $symfonyResponse->getStatusCode(),
            $symfonyResponse->headers->all(),
            $symfonyResponse->getContent()
        );
        $kernel->terminate($symfonyRequest, $symfonyResponse);

    /**
     * Catching errors and sending to syslog
     */
    } catch (\Exception $e) {
        openlog("search-server", LOG_PID, LOG_LOCAL0);
        syslog(LOG_ALERT, "[{$e->getFile()}] [{$e->getCode()}] ::: [{$e->getMessage()}]");
        closelog();

        throw $e;
    }

    return $httpResponse;
});

$http->listen($limitedServer);
$loop->run();
