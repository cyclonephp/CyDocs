<?php

namespace cyclone;

use cyclone\docs\model;
use cyclone\docs;

/**
 *
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 * @property-read boolean $internal
 * @property-read string $title
 * @property-read string $preface
 * @property-read boolean $line_numbers
 */
class Docs {

    /**
     * The singleton instance.
     *
     * @var Docs
     */
    private static $_inst;

    /**
     * The readonly properties of @c Docs
     *
     * @var array
     */
    private static $_enabled_attributes = array('internal'
    , 'title'
    , 'preface'
    , 'line_numbers');

    /**
     * @return Docs
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new Docs;
        }
        return self::$_inst;
    }

    /**
     *
     * @var boolean
     */
    private $_internal;

    /**
     * The documentation title passed by the <code>--title</code> CLI argument.
     *
     * @var string
     */
    private $_title;

    /**
     * The preface passed by the <code>--preface</code> CLI argument.
     *
     * @var string
     */
    private $_preface;

    /**
     *
     * @var boolean
     */
    private $_line_numbers;

    /**
     * The name of the class that is currently under processing.
     *
     * @var string
     * @usedby model\AbstractModel::coderef_to_anchor()
     * @usedby model\ClassModel
     */
    public $current_class;

    /**
     * The name of the library which the library manual is currently
     * generated for.
     *
     * @var string
     */
    public $current_library = NULL;

    public function __get($key) {
        if (in_array($key, self::$_enabled_attributes)) {
            return $this->{'_' . $key};
        }
        throw new PropertyAccessException(get_class($this), $key);
    }


    private function  __construct() {
        //empty private constructor
    }

    /**
     * Called by Cyclone CLI, see cydocs/cli.php for available commands.
     *
     * @param array $args
     */
    public function cli_api_bootstrap($args) {
        if ($args['--measure']) {
            $start_time = microtime(TRUE);
        }
        $this->_title = $args['--title'];
        $this->_preface = $args['--preface'];
        $this->_line_numbers = $args['--line-numbers'];
        $libs = explode(',', $args['--lib']);
        if ($libs[0] == 'all') {
            $libs = FileSystem::enabled_libs();
        }
        $this->_internal = $args['--internal'];
        $classnames = $this->load_classes($libs);
        $class_models = array();
        foreach ($classnames as $classname) {
            try {
                $class_models [] = model\AbstractModel::for_reflector(new \ReflectionClass($classname));
            } catch (\ReflectionException $ex) {
                log_warning($this, $ex->getMessage(), $ex->getCode());
            }
        }
        $lib_models = array();
        foreach ($libs as $lib_str) {
            $lib_models [] = new model\LibraryModel($lib_str);
        }
        $root_dir = $args['--output-dir'];
        $root_dir_len = strlen($root_dir);
        $root_dir_end = $root_dir{$root_dir_len - 1};
        if (!($root_dir_end == \DIRECTORY_SEPARATOR || $root_dir_end == '/')) {
            $root_dir .= \DIRECTORY_SEPARATOR;
        }
        if ($args['--forced']) {
            try {
                FileSystem::rmdir($root_dir);
            } catch (\Exception $ex) {
                // echo $ex->getMessage();
            }
        }

        mkdir($root_dir);
        if (count($libs) > 1) {
            $output = new docs\output\html\Output($root_dir, $lib_models, $args['--stylesheet']);
        } else {
            $output = new docs\output\html\LibraryOutput($root_dir, $lib_models[0], $args['--stylesheet']);
        }
        model\AbstractModel::set_root_path_provider($output);
        model\AbstractModel::fire_post_load();
        model\LibraryModel::fire_post_load();

        $output->generate_api();
        $output->generate_manual();
        if ($args['--measure']) {
            $time = microtime(TRUE) - $start_time;
            $mem_usage = (memory_get_peak_usage() / 1024) / 1024.0;

            // display only after the already registered shutdown functions - eg. the log adapter output
            /*register_shutdown_function(function() use($time, $mem_usage){
                echo sprintf("execution time: %.2f sec\tmax. memoy usage: %.2f Mb" . \PHP_EOL
                    , $time, $mem_usage);
            });*/
        }
    }

    public function load_classes($libs) {
        $class_files = FileSystem::list_directory('classes', $libs);
        return $this->extract_class_names($class_files);
    }

    public function extract_class_names($class_files) {
        $rval = array();
        foreach ($class_files as $abs_path => $val) {
            if (is_array($val)) {
                $sub_rval = $this->extract_class_names($val);
                foreach ($sub_rval as $sub_val) {
                    $rval [] = $sub_val;
                }
                continue;
            }
            $classname = substr($val, strpos($val, 'classes') + strlen('classes/'));
            if (substr($classname, strlen($classname) - strlen('.php')) != '.php') {
                continue;
            }
            $classname = substr($classname, 0, strlen($classname) - strlen('.php'));
            $classname = str_replace(DIRECTORY_SEPARATOR, '\\', $classname);
            $rval [] = $classname;
        }
        return $rval;
    }

}
