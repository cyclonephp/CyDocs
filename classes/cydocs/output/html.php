<?php

class CyDocs_Output_HTML implements CyDocs_Output {

    private $_root_dir;

    private $_lib_models;

    private $_lib_outputs = array();

    /**
     *
     * @param string $root_dir
     * @param array $_lib_models
     */
    public function  __construct($root_dir, $lib_models) {
        $this->_root_dir = $root_dir;
        $this->_lib_models = $lib_models;
    }

    public function generate() {
        mkdir($this->_root_dir . 'libs/');
        foreach ($this->_lib_models as $model) {
            $lib_output = new CyDocs_Output_HTML_Library(
                $this->_root_dir . 'libs/' . $model->name . '/', $model);
            $lib_output->generate();
            $this->_lib_outputs []= $lib_output;
        }
    }

}