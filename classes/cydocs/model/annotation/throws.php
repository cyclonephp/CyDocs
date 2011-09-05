<?php

class CyDocs_Model_Annotation_Throws extends CyDocs_Model_Annotation {

    /**
     * The class of the thrown annotation
     *
     * @var string
     */
    public $exception_class;

    protected function init() {
        if (count($this->_words) == 0) {
            log_warning($this, 'parameterless @throws annotation');
            return;
        }

        $this->exception_class = array_shift($this->_words);
        $this->text = implode(' ', $this->_words);
    }
    
}