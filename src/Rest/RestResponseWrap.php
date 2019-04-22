<?php

namespace OpenCore\Rest;

use Psr\Http\Message\ResponseInterface;

class RestResponseWrap {

    public $body;
    public $response;

    public static function of(ResponseInterface $response, $body) {
        $ret = new RestResponseWrap();
        $ret->body = $body;
        $ret->response = $response;
        return $ret;
    }

}
