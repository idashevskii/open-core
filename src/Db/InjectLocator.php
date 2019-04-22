<?php

namespace OpenCore\Db;

use Spot\Config;
use Spot\Locator;
use OpenCore\Db\InjectableMapper;
use OpenCore\Services\Injector;

class InjectLocator extends Locator {

    private $injector;

    public function __construct(Config $config, Injector $injector) {
        $ret = parent::__construct($config);
        $this->injector = $injector;
        return $ret;
    }

    public function mapper($entityName) {
        $ret = parent::mapper($entityName);
        if ($ret instanceof InjectableMapper) {
            if (!$ret->_injected) {
                $ret->_injected = true;
                $this->injector->injectCall($ret, 'inject');
            }
        }
        return $ret;
    }

}
