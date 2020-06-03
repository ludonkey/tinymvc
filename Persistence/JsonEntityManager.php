<?php

namespace ludk\Persistence;

use ReflectionProperty;
use ludk\Persistence\ObjectRepository;

final class JsonEntityManager implements ObjectManager
{
    private $allRepos;
    private $allDataToAddWhenFlush;
    private $allDataToRemoveWhenFlush;
    private $allDataToRemoveCascadeWhenFlush;
    private $resourcesDirPath;

    public function __construct($resourcesDirPath)
    {
        $this->allRepos = array();
        $this->allDataToAddWhenFlush = array();
        $this->allDataToRemoveWhenFlush = array();
        $this->allDataToRemoveCascadeWhenFlush = array();
        $this->resourcesDirPath = $resourcesDirPath;
    }

    public function getRepository($className): ObjectRepository
    {
        if (!array_key_exists($className, $this->allRepos)) {
            $this->allRepos[$className] = new JsonRepository($className, $this->resourcesDirPath);
        }
        return $this->allRepos[$className];
    }

    public function persist($object): void
    {
        $this->allDataToAddWhenFlush[] = $object;
    }

    public function remove($object, $cascade = false): void
    {
        if ($cascade) {
            $this->allDataToRemoveCascadeWhenFlush[] = $object;
        } else {
            $this->allDataToRemoveWhenFlush[] = $object;
        }
    }

    public function flush(): void
    {
        foreach ($this->allDataToAddWhenFlush as $oneObject) {
            if (empty($oneObject)) {
                continue;
            }
            $this->createOrUpdate($oneObject);
        }
        $this->allDataToAddWhenFlush = array();
        foreach ($this->allDataToRemoveWhenFlush as $oneObject) {
            if (empty($oneObject)) {
                continue;
            }
            $this->removeEntityIfExists($oneObject, false);
        }
        $this->allDataToRemoveWhenFlush = array();
        foreach ($this->allDataToRemoveCascadeWhenFlush as $oneObject) {
            if (empty($oneObject)) {
                continue;
            }
            $this->removeEntityIfExists($oneObject, true);
        }
        $this->allDataToRemoveCascadeWhenFlush = array();
    }

    private function createOrUpdate(&$oneObject)
    {
        $className = get_class($oneObject);
        $id = isset($oneObject->id) ? $oneObject->id : -1;
        if ($id >= 0) {
            $repo = $this->getRepository($className);
            $existingObject = $repo->find($id);
            if (!empty($existingObject)) {
                $this->updateEntity($oneObject);
            } else {
                $this->createEntity($oneObject);
            }
        } else {
            $oneObject->id = -1;
            $this->createEntity($oneObject);
        }
    }

    private function createEntity(&$oneObject)
    {
        $className = get_class($oneObject);
        foreach ($oneObject as $pName => &$pValue) {
            $reflection = new ReflectionProperty($className, $pName);
            $reflectionType = $reflection->getType();
            if (null != $reflectionType && !$reflectionType->isBuiltin()) {
                $this->createOrUpdate($pValue);
            }
        }
        $this->getRepository($className)->create($oneObject);
    }

    private function updateEntity(&$oneObject)
    {
        $className = get_class($oneObject);
        foreach ($oneObject as $pName => &$pValue) {
            $reflection = new ReflectionProperty($className, $pName);
            $reflectionType = $reflection->getType();
            if (null != $reflectionType && !$reflectionType->isBuiltin()) {
                $this->createOrUpdate($pValue);
            }
        }
        $this->getRepository($className)->update($oneObject);
    }

    private function removeEntityIfExists($oneObject, $cascade)
    {
        $className = get_class($oneObject);
        $id = $oneObject->id;
        if ($id >= 0) {
            $repo = $this->getRepository($className);
            $existingObject = $repo->find($id);
            if (!empty($existingObject)) {
                $this->removeEntity($oneObject, $cascade);
            }
        }
    }

    private function removeEntity(&$oneObject, $cascade)
    {
        $className = get_class($oneObject);
        if ($cascade) {
            foreach ($oneObject as $pName => &$pValue) {
                $reflection = new ReflectionProperty(get_class($className), $pName);
                $reflectionType = $reflection->getType();
                if (null != $reflectionType && !$reflectionType->isBuiltin()) {
                    $this->removeEntityIfExists($pValue, $cascade);
                }
            }
        }
        $this->getRepository($className)->remove($oneObject);
    }
}
