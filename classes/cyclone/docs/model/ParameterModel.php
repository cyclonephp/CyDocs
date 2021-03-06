<?php

namespace cyclone\docs\model;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class ParameterModel extends AbstractModel {

    /**
     * The type of the parameter.
     *
     * @var string
     */
    public $type;

    /**
     * The method that this parameter belongs to.
     *
     * @var CyDocs_Model_Method
     */
    public $method;

    /**
     * The default value of the represented parameter.
     *
     * @var scalar
     */
    public $default;

    /**
     * Flag marking that if the default value must be shown in the generated
     * output, or not.
     *
     * @var boolean
     */
    public $show_default = FALSE;

    /**
     * Flag marking that the parameter is passed by reference.
     *
     * @var boolean
     */
    public $by_ref = FALSE;

    public function  init() {
        $reflector = $this->reflector;
        $this->name = $reflector->getName();
        if ($reflector->getClass() != NULL) {
            $this->type = $reflector->getClass()->getName();
        }
        if ($reflector->isOptional()) {
            try {
                $this->default = $reflector->getDefaultValue();
                if (is_string($this->default)) {
                    $this->default = "'{$this->default}'"; // adding apostrophes to default string
                }
            } catch (\ReflectionException $ex) {
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
        if ($reflector->isArray()) {
            $this->type = 'array';
        }
        if ($reflector->isPassedByReference() && ! $reflector->getClass()) {
            $this->by_ref = TRUE;
        }
        $this->method = AbstractModel::for_reflector($reflector->getDeclaringFunction());
    }

    public function  post_loading() {
        parent::post_loading();
        $this->type = AbstractModel::coderef_to_anchor($this->type);
    }

    public function  string_identifier() {
        return $this->method->class->name . '::' . $this->method->name . '::' . $this->name;
    }

}
