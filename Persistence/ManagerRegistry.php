<?php

namespace ludk\Persistence;

use ludk\Http\Session;
use ludk\Persistence\ObjectManager;
use ludk\Persistence\ObjectRepository;

class ManagerRegistry
{
    const SESSION_KEY = 'OBJECT_MANAGER';

    private static $RESOURCES_DIR_PATH;

    public static function reset(): void
    {
        $session = new Session();
        if ($session->isStarted()) {
            $session->remove(self::SESSION_KEY);
        }
    }

    public static function setResourcesDirPath($resourcesDitPath)
    {
        self::$RESOURCES_DIR_PATH = $resourcesDitPath;
    }

    public static function getManager(): ObjectManager
    {
        $session = new Session();
        if (!$session->isStarted()) {
            $session->start();
        }
        if (!$session->has(self::SESSION_KEY)) {
            $session->set(self::SESSION_KEY, new JsonEntityManager(self::$RESOURCES_DIR_PATH));
        }
        return $session->get(self::SESSION_KEY);
    }

    public static function getRepository($classname): ObjectRepository
    {
        return ManagerRegistry::getManager()->getRepository($classname);
    }
}
