<?php

declare(strict_types = 1);

namespace OpenCore\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;
use Laminas\Diactoros\Response;
use Monolog\Logger;
use OpenCore\Rest\RestError;

class AuthMiddleware implements MiddlewareInterface {

    private $handlerPermissionsMap;
    private static $handlerAttribute = 'request-handler';
    private $privilegeChecker;
    private $logger;

    /**
     * Set the Dispatcher instance.
     */
    public function __construct(array $handlerPermissionsMap, Closure $privilegeChecker, Logger $logger) {
        $this->handlerPermissionsMap = $handlerPermissionsMap;
        $this->privilegeChecker = $privilegeChecker;
        $this->logger = $logger;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        $handlerName = $request->getAttribute(self::$handlerAttribute);
        $permission = $this->handlerPermissionsMap[$handlerName];

//        var_dump($permission);

        if (!($this->privilegeChecker)($permission)) {

            $this->logger->notice('Permission denied (' . $permission . ') for request handler "' . $handlerName . '"');

            $ret = (new Response())->withStatus(RestError::HTTP_FORBIDDEN);
        } else {
            $ret = $handler->handle($request);
        }
        return $ret;
    }

}
