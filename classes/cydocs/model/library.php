<?php

class CyDocs_Model_Library {

    private static $_instances = array();

    public static function add_class(CyDocs_Model_Class $class) {
        if (NULL === $class->library)
            throw new CyDocs_Exception("can't add class '{$class->name}' to any library");

        $libname = trim($class->library);
        if ( ! isset(self::$_instances[$libname]))
            throw new CyDocs_Exception("library '$libname' does not exist");

        self::$_instances[$libname]->classes []= $class;
    }

    public static function get_by_name($libname) {
        if ( ! isset(self::$_instances[$libname]))
            throw new CyDocs_Exception("library '$libname' does not exist");

        return self::$_instances[$libname];
    }

    public $name;

    public $classes = array();

    public function  __construct($lib_name) {
        $this->name = strtolower($lib_name);
        self::$_instances[strtolower($lib_name)] = $this;
    }

    
}