<?php

namespace cyclone\docs\model\annotation;

/**
 *
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class ThrowsAnnotation extends AbstractAnnotation {

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