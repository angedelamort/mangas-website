<?php

namespace mangaslib\models;


use mangaslib\db\Library;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseModel {

    const DB_NULL = "<NULL-893ebfc0-2f8e-44b0-ba1d-928ef25cfdaf>";

    /** @var \mysqli */
    private static $mysqli; // TODO: change to static since we can only have 1 instance. Have constructor with "UseSingleInstance = true"

    private static function __constructStatic() {
        $file = Library::getDbConfig();
        $ini = parse_ini_file($file, true);
        self::open($ini);
    }

    // static method to override in base class if necessary
    protected static function primaryKey() : string {
        return 'id';
    }

    // static method to override in base class if necessary
    protected static function tableName() : string {
        return get_called_class();
    }

    public static function findById($id) {
        $tableName = static::tableName();
        $primaryKey = static::primaryKey();
        $query = "SELECT * FROM $tableName WHERE $primaryKey='$id';";
        $result = self::query($query);
        return $result->fetch_object(get_called_class());
    }

    public static function deleteById($id) {
        $tableName = static::tableName();
        $primaryKey = static::primaryKey();
        $sql = "DELETE FROM $tableName WHERE $primaryKey='$id' LIMIT 1;";
        return self::query($sql);
    }

    public static function findAll($fields = '*', $cond = '1', $orderBy = null) {
        $tableName = static::tableName();
        $query = "SELECT $fields FROM $tableName WHERE $cond";
        if ($orderBy != null) {
            $query .= " ORDER BY $orderBy";
        }
        $query .= ';';
        $result = self::query($query);
        $items = [];
        while ($item = $result->fetch_object(get_called_class())) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param $array
     * @return mixed
     * @throws \ReflectionException
     */
    public static function createFromArray($array) {
        $class = get_called_class();
        $reflect = new ReflectionClass($class);
        $instance = new $class();
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (array_key_exists($prop->getName(), $array)) {
                $value = $array[$prop->getName()];
                $schema = new FieldSchema($reflect, $prop);
                if ($schema->getType() != "string") {
                    settype($value, $schema->getType());
                }
                $prop->setValue($instance, $value);
            }
        }
        return $instance;
    }

    private static function open($connectionStrings) {
        if (self::$mysqli)
            return;

        $default = $connectionStrings['default'];
        $port = 3306;
        if (array_key_exists('port', $default)) {
            $port = intval($default['port']);
        }
        self::$mysqli = new \mysqli($default['uri'], $default['username'], $default['password'], $default['dbname'], $port);

        if (self::$mysqli->connect_errno) {
            throw new \Exception(self::$mysqli->error);
        }

        self::$mysqli->query("SET NAMES 'utf8");
        self::$mysqli->query("SET CHARACTER SET utf8");
    }

    protected static function throwException($message, $query) {
        error_log("QUERY:");
        $maxLen = 800;
        for ($i = 0; $i < strlen($query); $i += $maxLen){
            error_log(substr($query, $i, $i + $maxLen));
        }
        error_log("ERROR MESSAGE-> $message");
        throw new \Exception($message);
    }

    protected static function count($field, $table, $where = '1') {
        $query = "SELECT COUNT($field) as total FROM $table WHERE $where;";
        $result = self::$mysqli->query($query) or selft::throwException(self::$mysqli->error, $query);
        $item = $result->fetch_assoc();
        return $item['total'];
    }

    protected static function query($query) {
        $result = self::$mysqli->query($query);
        if ($result) {
            return $result;
        }
        self::throwException(self::$mysqli->error, $query);
    }

    /**
     * If you table has auto-increment field, it will return the last value.
     * @return int
     */
    protected static function getLastInsertedId() {
        return self::$mysqli->insert_id;
    }

    protected static function escapeString($value) {
        return self::$mysqli->real_escape_string($value);
    }

    /**
     * @param BaseModel $model
     * @param string $table
     * @param string $condition
     * @return bool|\mysqli_result
     * @throws \ReflectionException
     */
    protected static function update(BaseModel $model, string $table, string $condition) {
        // TODO: do not set the primary key + generate the condition automatically.
        $reflect = new ReflectionClass($model);
        $values = [];
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $value = $prop->getValue($model);
            if ($value == null) {
                continue;
            } else if ($value == self::DB_NULL) {
                $value = 'null';
            }  else if (is_string($value)) {
                $value = '"' . self::escapeString($value) . '"';
            }
            $key = $prop->getName();
            $values[] = "$key=$value";
        }

        $query = "UPDATE $table SET " . implode(', ', $values) . " WHERE $condition LIMIT 1;";
        $result = self::$mysqli->query($query);

        return $result;
    }

    /**
     * @param $model
     * @param string $table
     * @param array|null $ignoreFields
     * @return bool|\mysqli_result
     * @throws \ReflectionException
     */
    protected static function insert($model, string $table, array $ignoreFields = null) {
        $reflect = new ReflectionClass($model);
        $fields = [];
        $values = [];
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (!$ignoreFields || !in_array($prop, $ignoreFields)) {
                $value = $prop->getValue($model);
                if ($value !== null)
                {
                    if (is_string($value)) {
                        $value = "'" . self::$mysqli->real_escape_string($value) . "'";
                    }
                    $values[] = $value;
                    $fields[] = $prop->getName();
                }
            }
        }

        $fields = join(", ", $fields);
        $values = join(", ", $values);
        $sql = "INSERT INTO $table ($fields) VALUES($values)";
        return self::$mysqli->query($sql);
    }

    /**
     * @param $classOrObject
     * @param array|null $ignoreFields List of properties you don't want to get.
     * @return string
     * @throws \ReflectionException
     */
    protected static function getFields($classOrObject, array $ignoreFields = null) {
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
