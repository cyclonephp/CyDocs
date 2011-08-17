<?php

class CyDocs_Model_Comment {

    public $text = array();

    public $annotations = array();

    public function annotations_by_name($name) {
        $rval = array();
        if (is_array($name)) {
            foreach ($this->annotations as $ann) {
                if (in_array($ann->name, $name)) {
                    $rval []= $ann;
                }
            }
            return $rval;
        }
        foreach ($this->annotations as $ann) {
            if ($ann->name == $name) {
                $rval []= $ann;
            }
        }
        return $rval;
    }
    
}