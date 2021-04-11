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

class CsrfProtection implements MiddlewareInterface {

    private static $headerName = 'x-csrf-token';
    private static $cookieName = 'CSRF_TOKEN';
    private $methodsSet;
    private $tokenProvider;
    private $logger;

    /**
     * Set the Dispatcher instance.
     */
    public function __construct(array $httpMethods, Closure $tokenProvider, Logger $logger) {
        $this->methodsSet = array_flip($httpMethods);
        $this->tokenProvider = $tokenProvider;
        $this->logger = $logger;
    }

    private function getToken() {
        return ($this->tokenProvider)();
    }

    private function checkTokenFromClient(ServerRequestInterface $request) {
        $values = $request->getHeader(self::$headerName);
        return $values && isset($values[0]) && $values[0] === $this->getToken();
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        if (isset($this->methodsSet[$request->getMethod()]) && !$this->checkTokenFromClient($request)) {

            $this->logger->notice('Invalid CSFR token');

            $ret = (new Response())->withStatus(RestError::HTTP_CONFLICT)->withHeader(self::$headerName, 'INVALID');
        } else {
            $ret = $handler->handle($request);
        }
        // chain to next middleware and set (possibe updated, during request hanling) token
        setcookie(self::$cookieName, $this->getToken(), 0, '/');

        return $ret;
//        return $ret->withHeader('Set-Cookie', self::$cookieName.'='.$this->getToken().'; path=/');
    }

}
