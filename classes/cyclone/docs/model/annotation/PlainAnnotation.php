<?php

namespace cyclone\docs\model\annotation;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class PlainAnnotation extends AbstractAnnotation {

    protected function init() {
        $this->text = implode(' ', $this->_words);
    }
    
}
