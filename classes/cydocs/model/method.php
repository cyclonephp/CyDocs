<?php

class CyDocs_Model_Method extends CyDocs_Model {

    public $is_static;

    public $class;

    public $is_abstract;

    public $is_final;

    public $visibility;

    public $is_constructor;

    public $is_destructor;

    public $parameters = array();

    public $return_type;
    

    public function init() {
        $reflector = $this->reflector;
        $this->is_static = $reflector->isStatic();
        $this->is_abstract = $reflector->isAbstract();
        $this->class = CyDocs_Model::for_reflector($reflector->getDeclaringClass());
        $this->name = $reflector->getName();
        $this->comment = $reflector->getDocComment();
        if ($reflector->isPublic()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PUBLIC;
        }
        if ($reflector->isProtected()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PROTECTED;
        }
        if ($reflector->isPrivate()) {
            $this->visibility = CyDocs_Model::VISIBILITY_PRIVATE;
        }

        $this->is_constructor = $reflector->isConstructor();
        $this->is_destructor = $reflector->isDestructor();

        foreach ($reflector->getParameters() as $ref_param) {
            $this->parameters []= CyDocs_Model::for_reflector($ref_param);
        }
    }

    public function post_loading() {
        
    }

}