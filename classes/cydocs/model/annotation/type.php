<?php

class CyDocs_Model_Annotation_Type extends CyDocs_Model_Annotation {

    public $formal_name;

    public $type;

    protected function init() {
        $word_count = count($this->_words);
        for ($i = 0; $i < 2; ++$i) {
            if ($i >= $word_count)
                break;
            if ($this->_words[$i]{0} == '$') {
                $this->formal_name = $this->_words[$i];
            } else {
                $this->type = $this->_words[$i];
            }
        }

        if (NULL === $this->formal_name) {
            log_warning($this, 'failed to determine name at ' . $this->_owner); // TODO improve
        }

        for(; $i < $word_count; ++$i) {
            $this->text .= $this->_words[$i] . ' ';
        }       
    }

}