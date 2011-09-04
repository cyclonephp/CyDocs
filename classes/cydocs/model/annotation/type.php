<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Annotation_Type extends CyDocs_Model_Annotation {

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
                } else {
                    $this->type = $this->_words[$i];
                }
            }
        }

        for(; $i < $word_count; ++$i) {
            $this->text .= $this->_words[$i] . ' ';
        }       
    }

}
