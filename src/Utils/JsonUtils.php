<?php

namespace OpenCore\Utils;

use JsonMapper;
use OpenCore\Json\JsonSerializer;

class JsonUtils {

    public static function mapJsonToObject($json, $object) {
        $jsonMapper = new JsonMapper();
        $jsonMapper->bStrictObjectTypeChecking = true;
        $jsonMapper->bExceptionOnUndefinedProperty = false;
        $jsonMapper->bStrictNullTypes = false;
        $jsonMapper->bExceptionOnMissingData = true;
        return $jsonMapper->map(json_decode($json), $object);
    }

    public static function mapObjectToJson($object) {
        $serializer = new JsonSerializer();
        return $serializer->serialize($object);
    }

}
