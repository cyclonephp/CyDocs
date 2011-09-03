<?php

class CyDocs_Output_HTML_Class implements CyDocs_Output {

    private $_root_dir;

    private $_model;

    private $_stylesheet;

    function __construct($root_dir, CyDocs_Model_Class $model, $stylesheet) {
        $this->_root_dir = $root_dir;
        $this->_model = $model;
        $this->_stylesheet = $stylesheet;
    }

    public function generate() {
        
    }

}