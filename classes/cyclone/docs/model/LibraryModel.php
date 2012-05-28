<?php

namespace cyclone\docs\model;

use cyclone\docs;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class LibraryModel {

    /**
     * Object pool of the created library instances.
     *
     * @var array<CyDocs_Model_Library>
     */
    private static $_instances = array();

    public static function add_class(ClassModel $class) {
        if (NULL === $class->library)
            throw new docs\Exception("can't add class '{$class->name}' to any library");

        $libname = trim($class->library);
        if ( ! isset(self::$_instances[$libname]))
            log_warning(__CLASS__, "library '$libname' does not exist, but class {$class->name} belongs to it");

        self::$_instances[$libname]->classes []= $class;
    }

    public static function get_by_name($libname) {
        if ( ! isset(self::$_instances[$libname]))
            throw new docs\Exception("library '$libname' does not exist");

        return self::$_instances[$libname];
    }

    /**
     * The name of the represented library.
     *
     * @var string
     */
    public $name;

    /**
     * Sequence of classes in the library.
     *
     * @var array<CyDocs_Model_Class>
     */
    public $classes = array();

    public function  __construct($lib_name) {
        $this->name = strtolower($lib_name);
        self::$_instances[strtolower($lib_name)] = $this;
    }

    public static function fire_post_load() {
        foreach (self::$_instances as $lib) {
            if ($lib instanceof LibraryModel) {
                $lib->post_loading();
            }
        }
    }

    public function post_loading() {
        uasort($this->classes, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
    }

    
}
