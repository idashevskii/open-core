<?php

namespace OpenCore\Services;

use ReflectionClass;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionParameter;

class Injector {

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    private function injectorMapParams($parameters, string $paramOwner) {
        $ret = [];
        foreach ($parameters as $param) {
            /* @var $param ReflectionParameter */
            $className = $param->getClass()->getName();
            if (!$this->container->has($className)) {
                throw new InvalidArgumentException('DI: Service ' . $className . ' not found for ' . $paramOwner);
            }
            $ret[] = $this->container->get($className);
        }
        return $ret;
    }

    public function inject(string $className) {
        $rflClass = new ReflectionClass($className);
        $rflConstructor = $rflClass->getConstructor();
        if ($rflConstructor) {
            $ret = $rflClass->newInstanceArgs($this->injectorMapParams($rflConstructor->getParameters(), $className));
        } else {
            $ret = $rflClass->newInstanceWithoutConstructor();
        }
        return $ret;
    }

    public function injectCall($object, string $methodName) {
        $rflClass = new ReflectionClass($object);
        if ($rflClass->hasMethod($methodName)) {
            $frlMethod = $rflClass->getMethod($methodName);
            $frlMethod->invokeArgs($object, $this->injectorMapParams($frlMethod->getParameters(), $rflClass->getName() . '.' . $methodName));
        }
    }

}
