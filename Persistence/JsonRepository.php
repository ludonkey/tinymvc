<?php

namespace ludk\Persistence;

use ReflectionClass;

class JsonRepository implements ObjectRepository
{
    private string $class;
    private array $objectsById;

    public function __construct(string $class, string $resourcesDirPath)
    {
        $this->objectsById = array();
        $this->class = $class;
        $reflection = new ReflectionClass($class);
        $jsonFile = $resourcesDirPath . DIRECTORY_SEPARATOR . $reflection->getShortName() . '.json';
        $jsonStr = file_get_contents($jsonFile);
        $jsonArray = json_decode($jsonStr, true);
        foreach ($jsonArray as $oneObjectJson) {
            $newObj = new $this->class();
            $newObj->loadFromJson($oneObjectJson);
            $this->objectsById[$newObj->id] = $newObj;
        }
    }

    public function getClassName()
    {
        return $this->class;
    }

    public function find($id)
    {
        if (array_key_exists($id, $this->objectsById))
            return $this->objectsById[$id];
        else
            return null;
    }

    public function findAll()
    {
        return array_values($this->objectsById);
    }

    public function findBy(array $criteria = [], array $orderBy = [], int $limit = -1, int $offset = 0)
    {
        $res = array();
        foreach ($this->objectsById as $key => $oneObj) {
            if (JsonRepository::isObjectValid($oneObj, $criteria)) {
                $res[] = $oneObj;
            }
        }
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $order) {
                usort($res, array(new MySortCallback($key, $order), "call"));
                break;
            }
        }
        if ($limit > -1) {
            $res = array_slice($res, $offset, $limit);
        }
        return $res;
    }

    public function count(array $criteria = [])
    {
        return count($this->findBy($criteria));
    }

    public function create(&$object)
    {
        $object->id = max(array_keys($this->objectsById)) + 1;
        $this->objectsById[$object->id] = $object;
    }

    public function update(&$object)
    {
        $this->objectsById[$object->id] = $object;
    }

    public function remove(&$object)
    {
        unset($this->objectsById[$object->id]);
    }

    private static function isObjectValid($obj, $criteria)
    {
        foreach ($criteria as $fieldName => $value) {
            if (preg_match('/' . $value . '/', $obj->$fieldName) == 0) {
                return false;
            }
        }
        return true;
    }
}

class MySortCallback
{
    private $key;
    private $order;

    function __construct($key, $order)
    {
        $this->key = $key;
        $this->order = $order;
    }

    function call($a, $b)
    {
        $v1 = $a->{$this->key};
        $v2 = $b->{$this->key};
        return strcmp($v1, $v2) * ($this->order == "ASC" ? 1 : -1);
    }
}
