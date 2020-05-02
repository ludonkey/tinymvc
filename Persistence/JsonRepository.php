<?php

namespace ludk\Persistence;

use Exception;
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
        if (!file_exists($jsonFile)) {
            throw new Exception("File doesn't exist: $jsonFile");
        }
        $jsonStr = file_get_contents($jsonFile);
        $jsonArray = json_decode($jsonStr, true);
        if (null == $jsonArray) {
            throw new Exception("Problem with json: $jsonFile " . JsonRepository::GetLastJsonError());
        }
        foreach ($jsonArray as $oneObjectJson) {
            $newObj = new $this->class();
            if (null == $newObj) {
                throw new Exception("Not able to instanciate " . $this->class);
            }
            if (!is_array($oneObjectJson)) {
                throw new Exception("Problem with json: $jsonFile should be an array of " . $this->class);
            }
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
        if ((is_string($id) || is_int($id)) && array_key_exists($id, $this->objectsById))
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
            if (!property_exists($obj, $fieldName)) {
                return false;
            }
            $valToTest = $obj->$fieldName;
            if (is_object($valToTest)) {
                $valToTest = $valToTest->id;
            }
            if (preg_match('/' . $value . '/', $valToTest) == 0) {
                return false;
            }
        }
        return true;
    }

    private static function GetLastJsonError(): string
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return ' - Unknown error';
                break;
        }
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