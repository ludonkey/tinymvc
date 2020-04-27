<?php

namespace ludk\Utils;

use ReflectionProperty;
use ludk\Persistence\ManagerRegistry;

trait Serializer
{
    public function loadFromJson($json)
    {
        foreach ($json as $key => $value) {
            $reflection = new ReflectionProperty(get_class($this), $key);
            $reflectionType = $reflection->getType();
            if ($reflectionType->isBuiltin()) {
                $this->{$key} = $value;
            } else {
                $id = $value;
                $classname = $reflectionType->getName();
                $subObj = ManagerRegistry::getRepository($classname)->find($id);
                $this->{$key} = $subObj;
            }
        }
    }
}