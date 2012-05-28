<?php

namespace cyclone\docs\output\html;

use cyclone\docs;
use cyclone\docs\model;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class ClassOutput implements docs\Output {

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

    function __construct($root_dir, model\ClassModel $model, $stylesheet) {
        $this->_root_dir = $root_dir;
        $this->_model = $model;
        $this->_stylesheet = $stylesheet;
    }

    public function generate_api() {
        
    }

    public function generate_manual() {
        throw new docs\Exception('manual generation for classes is not supported');
    }

}
