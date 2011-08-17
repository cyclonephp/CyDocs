<?php

class CyDocs_Model_Property extends CyDocs_Model {

    public $is_static;

    public $class;

    public $visibility;

    public $type;

    public function  __construct($reflector = NULL) {
        if ( ! is_null($reflector)) {
            parent::__construct($reflector);
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

    }

    
}