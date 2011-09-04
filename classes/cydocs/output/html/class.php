<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Output_HTML_Class implements CyDocs_Output {

    /**
     * @var string
     */
    private $_root_dir;

    /**
     * @var CyDocs_Model_Class
     */
    private $_model;

    /**
     * @var string
     */
    private $_stylesheet;

    function __construct($root_dir, CyDocs_Model_Class $model, $stylesheet) {
        $this->_root_dir = $root_dir;
        $this->_model = $model;
        $this->_stylesheet = $stylesheet;
    }

    public function generate() {
        
    }

}
