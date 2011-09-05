<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Output_HTML_Library implements CyDocs_Output {

    /**
     * The absolute path of the directory where the library docs should be
     * generated to.
     *
     * @var string
     */
    private $_root_dir;

    /**
     * The library instance that the documentation is generated for.
     *
     * @var CyDocs_Model_Library
     */
    private $_model;

    /**
     * The stylesheet to be applied on the documentation.
     *
     * @var string
     */
    private $_stylesheet;

    public function  __construct($root_dir, CyDocs_Model_Library $model, $stylesheet) {
        $this->_root_dir = $root_dir;
        $this->_model = $model;
        $this->_stylesheet = $stylesheet;
    }

    public function generate_api() {
        mkdir($this->_root_dir . 'classes/');
        $this->create_classes_html();
        $index_view = View::factory('cydocs/libs/index');
        file_put_contents($this->_root_dir . 'index.html', $index_view->render());
        copy($this->_stylesheet, $this->_root_dir . 'stylesheet.css');
        foreach ($this->_model->classes as $class) {
            $class_view = $this->create_class_view($class);
            
            $filepath = $this->_root_dir . $this->class_docs_file($class->name);
            $dirpath = substr($filepath, 0, strrpos($filepath, '/'));
            $class_output = new CyDocs_Output_HTML_Class($this->_root_dir . 'classes/'
                    , $class, $this->_stylesheet);
            $class_output->generate_api();
            if ( ! is_dir($dirpath)) {
                mkdir($dirpath, 0755, TRUE);
            }
            file_put_contents($filepath, $class_view->render());
        }
    }

    public function create_classes_html() {
        $classes_data = array();
        foreach ($this->_model->classes as $class_model) {
            $classes_data[$class_model->name] = $this->class_docs_file($class_model->name);
        }
        $classlist_view = View::factory('cydocs/libs/classes'
                , array('classes' => $classes_data));
        file_put_contents($this->_root_dir . 'classes.html', $classlist_view->render());
    }

    public function create_class_view(CyDocs_Model_Class $class) {
        CyDocs::inst()->current_class = $class->name;
        $view = View::factory('cydocs/libs/class');
        $view->set('class', $class);

        foreach ($class->implemented_interfaces as &$intf) {
            $intf = CyDocs_Model::coderef_to_anchor($intf->name);
        }

        foreach ($class->properties as $prop) {
            $prop->type = CyDocs_Model::coderef_to_anchor($prop->type);
        }

        $stylesheet_url = '../';
        $len = strlen($class->name);
        for ($i = 0; $i < $len; ++$i) {
            if ($class->name{$i} == '_') {
                $stylesheet_url .= '../';
            }
        }
        $view->set('properties', $this->create_property_list($class->properties));
        $stylesheet_url = $this->path_to_root($class->name) . 'stylesheet.css';
        $view->set('stylesheet_path', $stylesheet_url);
        CyDocs::inst()->current_class = NULL;
        return $view;
    }

    public static function class_docs_file($classname) {
        return 'classes/' . strtolower(str_replace('_', '/', $classname)) . '.html';
    }

    public static function path_to_root($classname) {
        $rval = '../';
        $len = strlen($classname);
        for ($i = 0; $i < $len; ++$i) {
            if ($classname{$i} == '_') {
                $rval .= '../';
            }
        }
        return $rval;
    }

    public function create_property_list($props) {
        
    }

    public function generate_manual() {
        $lib_root_path = FileSystem::get_root_path($this->_model->name);
        $manual_root_path = $lib_root_path . 'manual/';
        if (file_exists($manual_root_path . 'manual.txt')) {
            $formatter = CyDocs_Text_Formatter::manual_formatter(file_get_contents($manual_root_path . 'manual.txt'));
            $manual = $formatter->create_manual();
            $manual->title = $this->_model->name;
            file_put_contents($this->_root_dir . 'manual.html', $manual->render());
        } else {
            log_warning($this, "no manual found for library '{$this->_model->name}'");
        }
    }


}
