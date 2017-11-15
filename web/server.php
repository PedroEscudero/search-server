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


function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
}


$environment = 'prod';
$debug = false;
require __DIR__ . '/../vendor/autoload.php';


$oneBundleAppConfig = new \OneBundleApp\App\OneBundleAppConfig(__DIR__ . '/../', $environment);
$kernel = new \Mmoreram\BaseBundle\Kernel\BaseKernel(
    $oneBundleAppConfig->getBundles(),
    $oneBundleAppConfig->getConfig(),
    $oneBundleAppConfig->getRoutes(),
    $environment, $debug,
    realpath(__DIR__ . '/../var')
);

/**
 * REACT SERVER
 */
$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server($argv[1], $loop);
$http = new \React\Http\Server(function (\Psr\Http\Message\ServerRequestInterface $request) use ($kernel) {
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
        array(),
        array(), // To get the cookies, we'll need to parse the headers
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

    $symfonyResponse = $kernel->handle($symfonyRequest);
    return new \React\Http\Response(
        $symfonyResponse->getStatusCode(),
        $symfonyResponse->headers->all(),
        $symfonyResponse->getContent()
    );
});
$http->listen($socket);
$loop->run();
