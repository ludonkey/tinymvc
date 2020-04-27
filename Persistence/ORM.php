<?php

namespace ludk\Persistence;

use ludk\Persistence\ObjectManager;
use ludk\Persistence\ObjectRepository;

class ORM
{
    public function __construct($resourcesDirPath)
    {
        ManagerRegistry::SetResourcesDirPath($resourcesDirPath);
    }

    public function reset(): void
    {
        ManagerRegistry::reset();
    }

    public function getManager(): ObjectManager
    {
        return ManagerRegistry::getManager();
    }

    public function getRepository($classname): ObjectRepository
    {
        return ManagerRegistry::getRepository($classname);
    }
}
