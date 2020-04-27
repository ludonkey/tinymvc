<?php

namespace ludk\Persistence;

interface ObjectManager
{
    public function getRepository($className): ObjectRepository;
    public function persist($object): void;
    public function remove($object, $cascade = false): void;
    public function flush(): void;
    
}