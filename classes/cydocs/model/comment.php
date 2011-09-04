<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Comment {

    /**
     * Sequence of lines - the free-form text of the comment.
     *
     * @var array<string>
     */
    public $text = array();

    /**
     * The annotations found in the comment. The array is populated
     * by \c CyDocs_Parser::parse()
     *
     * @var array<CyDocs_Model_Annotation>
     */
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
