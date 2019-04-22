<?php

namespace OpenCore\Utils;

use Closure;

class FuncCollectionChain {

    private $collecton;

    public function value() {
        return $this->collecton;
    }

    public function filter(Closure $callback): FuncCollectionChain {
        $this->collecton = Collections::filter($this->collecton, $callback);
        return $this;
    }

    public function map(Closure $callback): FuncCollectionChain {
        $this->collecton = Collections::map($this->collecton, $callback);
        return $this;
    }

    public function __construct($collecton) {
        $this->collecton = $collecton;
    }

    public static function from($collecton) {
        return new Collections($collecton);
    }

}
