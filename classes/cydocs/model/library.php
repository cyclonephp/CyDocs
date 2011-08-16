<?php

class CyDocs_Model_Library {

    private static $_instances = array();

    public static function load_classes() {
        
    }

    public $name;

    public $classes = array();

    public function  __construct($lib_name) {
        $this->name = $lib_name;
        self::$_instances[$lib_name] = $this;
    }

    
}