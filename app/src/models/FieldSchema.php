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

    private $type = "string";
    private $editor = "textbox";
    private $readonly = false;

    public function __construct(ReflectionClass $reflect, ReflectionProperty $prop) {
        $this->reflect = $reflect;
        $this->prop = $prop;

        $schema = $this->reflect->getConstant($prop->getName() . '_schema');
        if ($schema !== FALSE) {
            if (array_key_exists('type', $schema)) {
                $this->type = $schema['type'];
            }
            if (array_key_exists('editor', $schema)) {
                $this->editor = $schema['editor'];
            }
            if (array_key_exists('readonly', $schema)) {
                $this->readonly = $schema['readonly'];
            }
        }
    }

    public function getType() {
        return $this->type;
    }

    public function getEditor() {
        return $this->editor;
    }

    public function isReadOnly() {
        return $this->readonly;
    }
}