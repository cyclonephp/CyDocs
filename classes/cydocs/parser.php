<?php

class CyDocs_Parser {

    public static $enabled_annotations = array('author'
        , 'package'
        , 'modified'
        , 'property'
        , 'property-read'
        , 'param'
        , 'usedby'
        , 'uses'
        , 'see'
        , 'link'
        , 'returns'
        , 'return'
        , 'copyright'
        , 'license'
        , 'var'
        , 'type'
        , 'access'
    );

    private $_plain_text;

    /**
     * @var CyDocs_Model
     */
    private $_owner;

    /**
     * The string representation of the "thing" that this doc comment belongs to
     * (can be a classname or classname::methodname or classname::propertyname)
     *
     * @var string
     */
    public $documented_code;

    public function  __construct($plain_text, CyDocs_Model $owner) {
        $this->_plain_text = $plain_text;
        $this->_owner = $owner;
    }

    /**
     * @return CyDocs_Model_Comment
     */
    public function parse() {
        $rval = new CyDocs_Model_Comment;
        $this->_plain_text = substr($this->_plain_text, 1, strlen($this->_plain_text) - 2);
        $lines = explode("\n", $this->_plain_text);

        $blank_line_passed = NULL; // blank line that should separate the free-form
            //description from the annotations
        $annotations_part = FALSE;
        $last_annotation = NULL;
        foreach ($lines as &$line) {
            $len = strlen($line);
            if ($len == 0)
                continue;
            // removing stars and whitespaces from the beginning of the lines
            for ($i = 0; $i < $len && ($line{$i} == ' ' || $line{$i} == "\t"); ++$i);
            $line = substr($line, $i);
            $len = strlen($line);
            for ($i = 0; $i < $len && ($line{$i} == '*'); ++$i);
            $line = substr($line, $i);

            $is_blank_line = trim($line) == '';
            if ($is_blank_line) {
                if (FALSE === $blank_line_passed) {
                    $blank_line_passed = TRUE;
                }
            } elseif (NULL === $blank_line_passed) {
                $blank_line_passed = FALSE;
            }
            
            if ($raw_annotation = self::may_be_annotation($line)) {
                if ($blank_line_passed) { // should be annotation
                    if ( ! in_array($raw_annotation[0], self::$enabled_annotations)) {
                        log_warning($this, 'unknown annotation ' . $raw_annotation[0] . ' at ' . $this->_owner->string_identifier());
                    } else {
                        $annotations_part = TRUE;
                        if ( ! is_null($last_annotation)) {
                            $rval->annotations []= $last_annotation;
                        }
                        $last_annotation = CyDocs_Model_Annotation::for_raw_annotation($raw_annotation, $this->_owner);
                    }
                } elseif (in_array($raw_annotation[0], self::$enabled_annotations)) {
                    $annotations_part = TRUE;
                    if (!is_null($last_annotation)) {
                        $rval->annotations [] = $last_annotation;
                    }
                    //echo $this->_owner->string_identifier() . PHP_EOL;
                    //if ($this->_owner->string_identifier() == 'DB_Expression::compile_expr()')
                    //print_r($raw_annotation);
                    $last_annotation = CyDocs_Model_Annotation::for_raw_annotation($raw_annotation, $this->_owner);
                }
            } elseif ($annotations_part) {
                $last_annotation->text .= $line;
            } else {
                $rval->text []= $line;
            }
        }
        if (!is_null($last_annotation)) {
            $rval->annotations [] = $last_annotation;
        }
        return $rval;
    }

    public static function may_be_annotation($line) {
        $len = strlen($line);
        for ($i = 0; $i < $len && ($line{$i} == ' ' || $line{$i} == "\t"); ++$i);
        if ($i >= $len || $line{$i} != '@')
            return NULL;
        $line = substr($line, $i + 1);
        return explode(' ', $line);
    }

    public function __invoke() {
        return $this->parse();
    }
    
}