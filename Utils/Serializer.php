<?php

namespace ludk\Utils;

use Exception;
use ReflectionProperty;
use ludk\Persistence\ManagerRegistry;

trait Serializer
{
    public function loadFromJson($json)
    {
        try {
            foreach ($json as $key => $value) {
                $reflection = new ReflectionProperty(get_class($this), $key);
                $reflectionType = $reflection->getType();
                if (null == $reflectionType || $reflectionType->isBuiltin()) {
                    $this->{$key} = $value;
                } else {
                    $id = $value;
                    $classname = $reflectionType->getName();
                    $subObj = ManagerRegistry::getRepository($classname)->find($id);
                    if (null == $subObj) {
                        throw new Exception("Object of type <$classname> with id (" . var_export($id, true) . ") doesn't exist");
                    }
                    $this->{$key} = $subObj;
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}