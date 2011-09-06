<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Annotation_Link extends CyDocs_Model_Annotation {

    public $link;

    protected function init() {
        if (count($this->_words) == 0) {
            log_warning("empty @uses annotation at " . $this->_owner->string_identifier());
            return;
        }

        $this->link = array_shift($this->_words);
        if (strpos($this->link, '://') !== FALSE) {
            $this->link = '<a href="' . $this->link . '">' . $this->link . '</a>';
        } else {
            $this->link = CyDocs_Model::coderef_to_anchor($this->link);
        }
        $this->text = implode(' ', $this->_words);
    }

}
