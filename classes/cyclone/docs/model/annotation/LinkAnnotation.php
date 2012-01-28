<?php

namespace cyclone\docs\model\annotation;

use cyclone\docs\model;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class LinkAnnotation extends AbstractAnnotation {

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
            $this->link = model\AbstractModel::coderef_to_anchor($this->link);
        }
        $this->text = implode(' ', $this->_words);
    }

}
