<?php

namespace ludk\Persistence;

use ludk\Persistence\ObjectManager;
use ludk\Persistence\ObjectRepository;

class ManagerRegistry
{
    const SESSION_KEY = 'OBJECT_MANAGER';

    private static $RESOURCES_DIR_PATH;

    public static function reset(): void
    {
        if (session_status() != PHP_SESSION_NONE) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }

    public static function setResourcesDirPath($resourcesDitPath)
    {
        self::$RESOURCES_DIR_PATH = $resourcesDitPath;
    }

    public static function getManager(): ObjectManager
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = new JsonEntityManager(self::$RESOURCES_DIR_PATH);
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function getRepository($classname): ObjectRepository
    {
        return ManagerRegistry::getManager()->getRepository($classname);
    }
}