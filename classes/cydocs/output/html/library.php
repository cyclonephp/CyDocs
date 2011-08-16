<?php

class CyDocs_Output_HTML_Library implements CyDocs_Output {

    private $_root_dir;

    private $_model;

    /**
     *
     * @param string $root_dir
     * @param array $_lib_models
     */
    public function  __construct($root_dir, CyDocs_Model_Library $model) {
        $this->_root_dir = $root_dir;
        $this->_model = $model;
    }

    public function generate() {
        mkdir($this->_root_dir . 'classes/');
        $this->create_classes_html();
    }

    public function create_classes_html() {
        $classes_data = array();
        foreach ($this->_model->classes as $class_model) {
            $classes_data[$class_model->name] = 'classes/' . strtolower(str_replace('_', '/', $class_model->name)) . '.html';
        }
        $classlist_view = View::factory('cydocs/libs/classes'
                , array('classes' => $classes_data));
        file_put_contents($this->_root_dir . 'classes.html', $classlist_view->render());
    }


}