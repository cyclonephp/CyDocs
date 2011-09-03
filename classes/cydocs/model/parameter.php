<?php

class CyDocs_Model_Parameter extends CyDocs_Model {

    public $type;

    public $method;

    public $default;

    public $show_default = FALSE;

    public function  init() {
        $reflector = $this->reflector;
        $this->name = $reflector->getName();
        if ($reflector->getClass() != NULL) {
            $this->type = $reflector->getClass()->getName();
        }
        if ($reflector->isOptional()) {
            try {
                $this->default = $reflector->getDefaultValue();
            } catch (ReflectionException $ex) {
                //print_r(xdebug_get_function_stack());
            }
            if (NULL === $reflector->getDefaultValue()) {
                if ($reflector->allowsNull()) {
                    $this->show_default = TRUE;
                    $this->default = 'NULL';
                } else {
                    
                }
            } else {
                $this->show_default = TRUE;
            }
        }
        $this->method = CyDocs_Model::for_reflector($reflector->getDeclaringFunction());
    }

    public function  post_loading() {
        parent::post_loading();
        $this->type = CyDocs_Model::coderef_to_anchor($this->type);
    }

    public function  string_identifier() {
        return $this->method->class->name . '::' . $this->method->name . '::' . $this->name;
    }

}