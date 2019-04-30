<?php

namespace OpenCore\Json;

use ReflectionClass;
use ReflectionProperty;

class JsonSerializer {

    private $classPropertyGetters = array();

    public function serialize($object) {
        return json_encode($this->serializeToAssoc($object), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function serializeToAssoc($object) {
        if (is_array($object)) {
            $result = $this->serializeArray($object);
        } elseif (is_object($object)) {
            $result = $this->serializeObject($object);
        } else {
            $result = $object;
        }
        return $result;
    }

    private function getClassPropertyGetters($object) {
        $className = get_class($object);
        if (!isset($this->classPropertyGetters[$className])) {
            $reflector = new ReflectionClass($className);
            $reflProperties = $reflector->getProperties();
            $getters = array();
            $properties = [];
            foreach ($reflProperties as $property) {
                $name = $property->getName();
                /* @var $property ReflectionProperty */
                if ($property->isPublic()) {
                    $properties[] = $name;
                } else {
                    $getter = 'get' . ucfirst($name);
                    if ($reflector->hasMethod($getter)) {
                        $getters[$name] = $getter;
                    }
                }
            }
            $this->classPropertyGetters[$className] = ['getters' => $getters, 'properties' => $properties];
        }
        return $this->classPropertyGetters[$className];
    }

    private function serializeObject($object) {
        $properties = $this->getClassPropertyGetters($object);
        $data = array();
        foreach ($properties['getters'] as $name => $getter) {
            $data[$name] = $this->serializeToAssoc($object->$getter());
        }
        foreach ($properties['properties'] as $property) {
            $data[$property] = $this->serializeToAssoc($object->$property);
        }
        return $data;
    }

    private function serializeArray($array) {
        $result = array();
        foreach ($array as $key => $value) {
            $result[$key] = $this->serializeToAssoc($value);
        }
        return $result;
    }

}
