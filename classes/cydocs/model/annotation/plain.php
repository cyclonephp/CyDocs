<?php

class CyDocs_Model_Annotation_Plain extends CyDocs_Model_Annotation {

    protected function init() {
        $this->text = implode(' ', $this->_words);
    }
    
}