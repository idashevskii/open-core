<?php

declare(strict_types = 1);

namespace OpenCore\Middlewares;

use Middlewares\Utils\Traits\HasResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrailingSlashMiddleware implements MiddlewareInterface {

    use HasResponseFactory;

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $uri = $request->getUri();
        return $handler->handle($request->withUri($uri->withPath(rtrim($uri->getPath(), '/'))));
    }

}
