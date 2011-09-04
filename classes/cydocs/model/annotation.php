<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
abstract class CyDocs_Model_Annotation {

    /**
     * @param array $words
     */
    public static function for_raw_annotation($words, $owner) {
        $annotation_name = $words[0];
        if ( ! in_array($annotation_name, CyDocs_Parser::$enabled_annotations))
                throw new CyDocs_Exception("unknown annotation: $annotation_name");

        $class_suffixes = array('author' => 'plain'
            , 'package' => 'plain'
            , 'modified' => 'plain'
            , 'property' => 'type'
            , 'property-read' => 'type'
            , 'param' => 'type'
            , 'usedby' => 'link'
            , 'uses' => 'link'
            , 'see' => 'link'
            , 'link' => 'link'
            , 'returns' => 'type'
            , 'return' => 'type'
            , 'copyright' => 'plain'
            , 'license' => 'plain'
            , 'var' => 'type'
            , 'type' => 'type'
            , 'access' => 'plain'
        );

        $class = 'CyDocs_Model_Annotation_' . $class_suffixes[$annotation_name];
        if ( ! class_exists($class))
            throw new Exception("error while processing annotation: $annotation_name");

        $inst = new $class($words, $owner);
        return $inst;
    }

    /**
     * The name of the annotation, not including the leading '@' character
     *
     * @var string
     */
    public $name;

    /**
     * The free-form text of the annotation.
     *
     * @var string
     */
    public $text;

    /**
     * Sequence of words that follows the annotation name.
     *
     * @var array
     */
    protected $_words;

    /**
     * A model instance that the annotation belongs to.
     *
     * @var CyDocs_Model
     */
    protected $_owner;

    private function  __construct($words, CyDocs_Model $owner) {
        $this->name = array_shift($words);
        $this->_words = $words;
        $this->_owner = $owner;
        $this->init();
    }

    protected abstract function init();
}
