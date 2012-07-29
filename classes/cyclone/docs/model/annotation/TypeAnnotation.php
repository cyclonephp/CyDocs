<?php

namespace cyclone\docs\model\annotation;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class TypeAnnotation extends AbstractAnnotation {

    /**
     * The formal name of the tool (property or method parameter) that's type
     * is defined by this annotation.
     *
     * @var string
     */
    public $formal_name;

    /**
     * @var string
     */
    public $type;

    protected function init() {
        $word_count = count($this->_words);
        for ($i = 0; $i < 2; ++$i) {
            if ($i >= $word_count)
                break;
            if (strlen($this->_words[$i]) > 0) {
                if ($this->_words[$i][0] == '$') {
                    $this->formal_name = $this->_words[$i];
                } elseif ($this->type === NULL) {
                    $this->type = $this->_words[$i];
                } else {
                    $this->text .= $this->_words[$i] . ' ';
                }
            }
        }

        for(; $i < $word_count; ++$i) {
            $this->text .= $this->_words[$i] . ' ';
        }       
    }

}
