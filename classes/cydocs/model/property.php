<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Property extends CyDocs_Model {

    /**
     * The logger instance.
     *
     * @var Log_Adapter
     */
    private static $_log;

    /**
     * Flag marking that the property is static.
     *
     * @var boolean
     */
    public $is_static;

    /**
     * The class that this property belongs to.
     *
     * @var CyDocs_Model_Class
     */
    public $class;

    /**
     * The visibility of the property (see \c CyDocs_Model constants).
     *
     * @var string
     */
    public $visibility;

    /**
     * The type of the property.
     *
     * @var string
     */
    public $type;

    public function  __construct($reflector = NULL) {
        if ( ! is_null($reflector)) {
            parent::__construct($reflector);
        }
        if (NULL === self::$_log) {
            self::$_log = Log::for_class($this);
        }
    }

    public function init() {
        $this->name = $this->reflector->getName();
        $this->comment = $this->reflector->getDocComment();
        $this->is_static = $this->reflector->isStatic();
        $this->class = CyDocs_Model::for_reflector($this->reflector->getDeclaringClass());
        if ($this->reflector->isPublic()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PUBLIC;
        }
        if ($this->reflector->isProtected()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PROTECTED;
        }
        if ($this->reflector->isPrivate()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PRIVATE;
        }
    }

    public function  post_loading() {
        parent::post_loading();
        $parser = new CyDocs_Parser($this->comment, $this);
        $comment = $parser->parse();
        $var_annots = $comment->annotations_by_name('var');
        if (count($var_annots) > 1) {
            self::$_log->add_error('ambiguous property type at ' . $this->class->name . '::' . $this->name);
        }
        if (count($var_annots) >= 1) {
            $var_annot = $var_annots[0];
            $this->type = $var_annot->type;
        } else {
            self::$_log->add_error('unknown property type at ' . $this->class->name . '::' . $this->name);
        }
        parent::process_links();
    }

    public function  string_identifier() {
        return $this->class->name . '::$' . $this->name;
    }

    
}
