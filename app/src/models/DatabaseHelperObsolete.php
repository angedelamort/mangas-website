<?php

namespace mangaslib\models;

use mangaslib\db\Library;
use ReflectionClass;
use ReflectionProperty;

class DatabaseHelperObsolete {
    /** @var \mysqli */
    private $mysqli; // TODO: change to static since we can only have 1 instance. Have constructor with "UseSingleInstance = true"

    public static function getDbConfig(){
        return dirname(dirname(dirname(__DIR__))) . '/db.ini';
    }

    function __construct() {
        $file = Library::getDbConfig();
        $ini = parse_ini_file($file, true);
        $this->open($ini);
    }

    public function query($query) {
        $result = $this->mysqli->query($query);
        if ($result) {
            return $result;
        }
        $this->throwException($this->mysqli->error, $query);
    }

    public function getLastInsertedId() {
        return $this->mysqli->insert_id;
    }

    public function escapeString($value) {
        return $this->mysqli->real_escape_string($value);
    }

    public function arrayToInsert($table, $item) {
        $values = [];
        foreach ($item as $value) {
            $values[] =  is_string($value) ? ('"' . $this->escapeString($value) . '"') : $value;
        }

        return "INSERT INTO $table (" . implode(', ', array_keys($item)) . ') VALUES (' . implode(', ', $values) . ')' ;
    }

    public function arrayToUpdate($table, $item, $condition) {
        $that = $this;
        array_walk($item, function(&$value, $key) use($that) {
            if (is_string($value)) {
                $value = $that->escapeString($value);
            }
            $value="$key=\"$value\"";
        });
        return "UPDATE $table SET " . implode(', ', $item) . " WHERE $condition LIMIT 1;";
    }

    public function count($field, $table, $where = '1') {
        $query = "SELECT COUNT($field) as total FROM $table WHERE $where;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $item = $result->fetch_assoc();
        return $item['total'];
    }

    public static function testConnection($uri, $username, $password, $dbName, $port) {
        try {
            $connection = @new \mysqli($uri, $username, $password, $dbName, intval($port));
            if ($connection->connect_errno) {
                return false;
            }

            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    private function open($connectionStrings) {
        if ($this->mysqli)
            return;

        $default = $connectionStrings['default'];
        $port = 3306;
        if (array_key_exists('port', $default)) {
            $port = intval($default['port']);
        }
        $this->mysqli = new \mysqli($default['uri'], $default['username'], $default['password'], $default['dbname'], $port);

        if ($this->mysqli->connect_errno) {
            throw new \Exception($this->mysqli->error);
        }

        $this->mysqli->query("SET NAMES 'utf8");
        $this->mysqli->query("SET CHARACTER SET utf8");
    }

    public function close() {
        if ($this->mysqli) {
            $this->mysqli->close();
            $this->mysqli = null;
        }
    }

    private function throwException($message, $query) {
        error_log("QUERY->");
        $maxLen = 800;
        for ($i = 0; $i < strlen($query); $i += $maxLen){
            error_log(substr($query, $i, $i + $maxLen));
        }
        error_log("ERROR MESSAGE-> $message");
        throw new \Exception($message);
    }

    /**
     * @param $model
     * @param string $table
     * @param array|null $ignoreFields
     * @return string
     * @throws \ReflectionException
     */
    public function objectToInsert($model, string $table, array $ignoreFields = null) {
        $reflect = new ReflectionClass($model);
        $fields = [];
        $values = [];
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (!$ignoreFields || !in_array($prop, $ignoreFields)) {
                $value = $prop->getValue($model);
                if ($value !== null)
                {
                    if (is_string($value)) {
                        $value = "'" . $this->mysqli->real_escape_string($value) . "'";
                    }
                    $values[] = $value;
                    $fields[] = $prop->getName();
                }
            }
        }

        $fields = join(", ", $fields);
        $values = join(", ", $values);
        return "INSERT INTO $table ($fields) VALUES($values)";
    }

    /**
     * @param $classOrObject
     * @param array|null $ignoreFields List of properties you don't want to get.
     * @return string
     * @throws \ReflectionException
     */
    public static function getFields($classOrObject, array $ignoreFields = null) {
        $reflect = new ReflectionClass($classOrObject);
        $array = [];
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (!$ignoreFields || !in_array($prop, $ignoreFields)) {
                $array[] = $prop->getName();
            }
        }
        return join(', ', $array);
    }
}