<?php

namespace mangaslib\models;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class Schema
 * The goal of this class is to provide helper for the BaseModel and define extra information to
 * the different fields.
 *
 * Usage:
 * public $myProp;
 * const myProp_schema = [...]
 *
 * Schema Fields:
 *  - type: @see https://www.php.net/manual/en/function.settype.php for the enumaration. Doesn't support array, object or null.
 *
 * Example:
 *   const myProp_schema = ['type' => 'int']
 *
 * @package mangaslib\models
 */
class FieldSchema {

    /** @var ReflectionClass */
    private $reflect;
    /**
     * @var ReflectionProperty
     */
    private $prop;

    private $hasType;
    private $type;

    public function __construct(ReflectionClass $reflect, ReflectionProperty $prop) {
        $this->reflect = $reflect;
        $this->prop = $prop;

        $schema = $this->reflect->getConstant($prop->getName() . '_schema');
        if ($schema === FALSE) {
            $$this->type = "string";
            $$this->hasType = false;
        } else if (!in_array('type', $schema)) {
            $$this->type = "string";
            $$this->hasType = true;
        } else {
            $$this->type = $schema['type'];
            $$this->hasType = true;
        }
    }

    public function hasType() {
        return $this->hasType;
    }

    public function getType() {
        return $this->type;
    }
}