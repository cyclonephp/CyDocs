<?php

class CyDocs_Model_Class extends CyDocs_Model {

    public $library;

    public $parent_class;

    public $implemented_interfaces = array();

    public $constants = array();

    public $static_properties;

    public $properties = array();

    public $methods = array();

    public $is_final;

    public $is_abstract;

    public $is_interface;

    public function init() {
        $reflector = $this->reflector;
        $this->name = $reflector->getName();
        if (($parent_class = $reflector->getParentClass()) != FALSE && ! $parent_class->isInternal()) {
            $this->parent_class = CyDocs_Model::for_reflector($parent_class);
        }
        $this->is_final = $reflector->isFinal();
        $this->is_abstract = $reflector->isAbstract();
        $this->is_interface = $reflector->isInterface();
        $this->comment = $reflector->getDocComment();
        $this->reflector = $reflector;
        foreach ($reflector->getInterfaces() as $intf) {
            $this->implemented_interfaces []= CyDocs_Model::for_reflector($intf);
        }
        foreach ($reflector->getConstants() as $name) {
            $this->constants[$name] = $reflector->getConstant($name);
        }
        foreach ($reflector->getStaticProperties() as $ref_prop) {
            //$this->static_properties []= CyDocs_Model::for_reflector($ref_prop);
        }
        foreach ($reflector->getProperties() as $ref_prop) {
            $this->properties []= CyDocs_Model::for_reflector($ref_prop);
        }
        foreach ($reflector->getMethods() as $ref_method) {
            $this->methods []= CyDocs_Model::for_reflector($ref_method);
        }
    }

    public function  post_loading() {

    }

}