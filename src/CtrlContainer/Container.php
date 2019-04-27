<?php

namespace OpenCore\CtrlContainer;

use Exception;
use Psr\Container\ContainerInterface;
use OpenCore\Rest\RestError;
use ReflectionClass;
use ReflectionParameter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenCore\Utils\JsonUtils;
use OpenCore\Services\Injector;
use OpenCore\Rest\RestResponseWrap;
use Monolog\Logger;

class Container implements ContainerInterface {

    /**
     * @var string
     */
    private $ns;

    /**
     * @var ContainerInterface
     */
    private $servicesContainer = null;
    private $transationMethods = null;
    private $transationCallback = null;
    private $defaultContentType = null;
    
    /**
     * @var Logger 
     */
    private $logger=null;

    public function __construct(array $options) {
        $this->ns = isset($options['ns']) ? $options['ns'] : '';
        $this->defaultContentType = isset($options['defaultContentType']) ? $options['defaultContentType'] : 'json';
        if (isset($options['servicesContainer'])) {
            $this->servicesContainer = $options['servicesContainer'];
        }
        if (isset($options['transation'])) {
            $this->transationMethods = $options['transation']['methods'];
            $this->transationCallback = $options['transation']['callback'];
        }
        if(isset($options['logger'])){
            $this->logger=$options['logger'];
        }
    }

    public function get($id) {
        list($ctrlName, $ctrlMethod) = explode('.', $id);
        $className = $this->ns . '\\' . $ctrlName;

        $injector = $this->servicesContainer->get(Injector::class);

        $ctrlInstance = $injector->inject($className);

        return function(ServerRequestInterface $request)use($ctrlInstance, $ctrlMethod) {
            return $this->handleRequest($ctrlInstance, $ctrlMethod, $request, $this->servicesContainer->get('response'));
        };
    }
    
    private function handleRequest($ctrl, $ctrlMethod, ServerRequestInterface $request, ResponseInterface $response) {
        $resBody = null;
        try {

            $reflClass = new ReflectionClass($ctrl);
            $reflMethod = $reflClass->getMethod($ctrlMethod);
            $reflParams = $reflMethod->getParameters();

            $args = [];

            $queryParams = $request->getQueryParams();

            foreach ($reflParams as $reflParam) {
                /* @var $reflParam ReflectionParameter */
                $paramName = $reflParam->getName();
                $paramClass = $reflParam->hasType() ? $reflParam->getClass() : null;
                $paramClassName = $paramClass ? $paramClass->getName() : null;
                if ($paramClassName === ServerRequestInterface::class) {
                    $args[] = $request;
                } else if ($paramClassName === ResponseInterface::class) {
                    $args[] = $response;
                } else if ($paramName === 'body') {
                    $args[] = JsonUtils::mapJsonToObject($request->getBody(), $paramClass->newInstanceWithoutConstructor());
                } else {
                    $val = $request->getAttribute($paramName, null);
                    if ($val === null && isset($queryParams[$paramName])) {
                        $val = $queryParams[$paramName];
                    }
                    if ($val !== null) {
                        switch ($reflParam->getType()) {
                            case 'bool': $val = ($val === 'true');
                                break;
                            case 'int': $val = (int) $val;
                                break;
                        }
                    }
                    $args[] = $val;
                }
            }

            $callMethod = function()use($reflMethod, $ctrl, $args) {
                return $reflMethod->invokeArgs($ctrl, $args);
            };
            if ($this->transationMethods && in_array($request->getMethod(), $this->transationMethods)) {
                $resBody = ($this->transationCallback)($callMethod);
            } else {
                $resBody = $callMethod();
            }

            if ($resBody && $resBody instanceof RestResponseWrap) {
                $response = $resBody->response;
                $resBody = $resBody->body;
            }
        } catch (RestError $ex) {
            $response = $response->withStatus($ex->getCode());
            $resBody = ['message' => $ex->getMessage()];
        } catch (Exception $ex) {
            $response = $response->withStatus(RestError::HTTP_INTERNAL_SERVER_ERROR);
            $resBody = ['message' => 'Unexpected error'];
            if($this->logger){
                try{
                    $this->logger->error($ex);
                } catch (Exception $ex) {}
            }
        }
        $contentType=$this->defaultContentType;
        if($contentType==='json'){
            if ($resBody !== null) {
                $response->getBody()->write(JsonUtils::mapObjectToJson($resBody));
            }
            $response = $response->withHeader('Content-Type', 'application/json');
        }else if($contentType==='text'){
            $response->getBody()->write(is_array($resBody) ? reset($resBody) : $resBody);
            $response = $response->withHeader('Content-Type', 'text/html');
        }
        return $response;
    }

    public function has($id): bool {
        return true;
    }

}
