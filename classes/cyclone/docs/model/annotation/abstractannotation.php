<?php

namespace cyclone\docs\model\annotation;

use cyclone\docs;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
abstract class AbstractAnnotation {

    /**
     * @param array $words
     */
    public static function for_raw_annotation($words, $owner) {
        $annotation_name = $words[0];
        if ( ! in_array($annotation_name, docs\Parser::$enabled_annotations))
                throw new docs\Exception("unknown annotation: $annotation_name");

        $class_prefixes = array('author' => 'plain'
            , 'package' => 'plain'
            , 'modified' => 'plain'
            , 'property' => 'type'
            , 'property-read' => 'type'
            , 'param' => 'param'
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
            , 'throws' => 'throws'
        );

        $class = 'cyclone\docs\model\annotation\\' . $class_prefixes[$annotation_name] . 'Annotation';
        if ( ! class_exists($class))
            throw new docs\Exception("error while processing annotation: $annotation_name");

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

    private function  __construct($words, docs\model\AbstractModel $owner) {
        $this->name = array_shift($words);
        $this->_words = self::clear_empty_words($words);
        $this->_owner = $owner;
        $this->init();
    }

    public static function clear_empty_words($words) {
        $rval = array();
        foreach ($words as $word) {
            if ($word === '') {
                
            } else {
                $rval []= $word;
            }
        }
        return $rval;
    }

    protected abstract function init();

    public function  __toString() {
        return '@' . $this->name . ' ' . implode(' ', $this->_words);
    }
}
