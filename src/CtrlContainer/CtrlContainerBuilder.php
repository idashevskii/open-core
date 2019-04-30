<?php

namespace OpenCore\CtrlContainer;

use Psr\Container\ContainerInterface;
use Closure;
use Psr\Log\LoggerInterface;

class CtrlContainerBuilder {

    private $options = [];

    public function __construct() {
        
    }

    public function useNamespace($ns) {
        $this->options['ns'] = $ns;
    }

    public function useLogger(LoggerInterface $logger) {
        $this->options['logger'] = $logger;
    }

    public function useServicesContainer(ContainerInterface $servicesContainer) {
        $this->options['servicesContainer'] = $servicesContainer;
    }

    public function useTextContentType() {
        $this->options['defaultContentType'] = 'text';
    }

    public function useDbTransations(array $httpMethods, Closure $callback) {
        $this->options['transation'] = ['methods' => $httpMethods, 'callback' => $callback];
    }

    public function build(): ContainerInterface {
        return new Container($this->options);
    }

}
