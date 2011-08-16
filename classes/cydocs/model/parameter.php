<?php

class CyDocs_Model_Parameter extends CyDocs_Model {

    public $type;

    public $method;

    public $default;

    public function  init() {
        $reflector = $this->reflector;
        $this->name = $reflector->getName();
        $this->type = $reflector->getClass();
        if ($reflector->isOptional()) {
            try {
                $this->default = $reflector->getDefaultValue();
            } catch (ReflectionException $ex) {
                //print_r(xdebug_get_function_stack());
            }
        }
        $this->method = CyDocs_Model::for_reflector($reflector->getDeclaringFunction());
    }

    public function  post_loading() {
        
    }

}