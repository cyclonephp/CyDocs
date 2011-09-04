<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 * @property-read boolean $internal
 */
class CyDocs {

    /**
     * The singleton instance.
     *
     * @var CyDocs
     */
    private static $_inst;

    /**
     * The readonly properties of \c CyDocs
     *
     * @var array
     */
    private static $_enabled_attributes = array('internal');

    /**
     * @return CyDocs
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new CyDocs;
        }
        return self::$_inst;
    }

    /**
     *
     * @var boolean
     */
    private $_internal;

    /**
     * The name of the class that is currently under processing.
     *
     * @var string
     * @usedby CyDocs_Model::coderef_to_anchor()
     * @usedby CyDocs_Model_Class
     */
    public $current_class;

    public function __get($key) {
        if (in_array($key, self::$_enabled_attributes)) {
            return $this->{'_' . $key};
        }
        throw new CyDocs_Exception("attribute '$key' does not exist or is not readable");
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
        $libs = explode(',', $args['--lib']);
        if ($libs[0] == 'all') {
            $libs = FileSystem::enabled_libs();
        }
        $this->_internal = $args['--internal'];
        $classnames = $this->load_classes($libs);
        $class_models = array();
        foreach ($classnames as $classname) {
            try {
                $class_models []= CyDocs_Model::for_reflector(new ReflectionClass($classname));
            } catch (ReflectionException $ex) {
                log_warning($this, $ex->getMessage(), $ex->getCode());
            }
        }

        $lib_models = array();
        foreach ($libs as $lib_str) {
            $lib_models []= new CyDocs_Model_Library($lib_str);
        }
        
        CyDocs_Model::fire_post_load();

        $root_dir = $args['--root-dir'];
        if ($args['--forced']) {
            try {
                FileSystem::rmdir($root_dir);
            } catch (Exception $ex) {
                echo $ex->getMessage();
            }
        }
        
        mkdir($root_dir);
        if (count($libs) > 1) {
            $output = new CyDocs_Output_HTML($root_dir, $lib_models, $args['--stylesheet']);
        } else {
            $output = new CyDocs_Output_HTML_Library($root_dir, $lib_models[0], $args['--stylesheet']);
        }
        $output->generate();
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
                    $rval []= $sub_val;
                }
                continue;
            }
            $classname = substr($val, strpos($val, 'classes') + strlen('classes/'));
            if (substr($classname, strlen($classname) - strlen('.php')) != '.php') {
                continue;
            }
            $classname = substr($classname, 0, strlen($classname) - strlen('.php'));
            $classname = str_replace(DIRECTORY_SEPARATOR, '_', $classname);
            $rval []= $classname;
        }
        return $rval;
    }
    
}