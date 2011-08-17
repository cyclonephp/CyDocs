<?php

class CyDocs {
    
    private static $_inst;

    /**
     * @return CyDocs
     */
    public static function inst() {
        if (NULL === self::$_inst) {
            self::$_inst = new CyDocs;
        }
        return self::$_inst;
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
        $classnames = $this->load_classes($libs);
        $class_models = array();
        foreach ($classnames as $classname) {
           $class_models []= CyDocs_Model::for_reflector(new ReflectionClass($classname));
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
            $output = new CyDocs_Output_HTML($root_dir, $lib_models);
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
            $classname = substr($classname, 0, strlen($classname) - strlen('.php'));
            $classname = str_replace(DIRECTORY_SEPARATOR, '_', $classname);
            $rval []= $classname;
        }
        return $rval;
    }
    
}