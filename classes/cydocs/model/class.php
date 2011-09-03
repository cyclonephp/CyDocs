<?php

class CyDocs_Model_Class extends CyDocs_Model {

    public $library;

    public $parent_class;

    public $subclasses = array();

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
        CyDocs::inst()->current_class = $this->name = $reflector->getName();
        if (($parent_class = $reflector->getParentClass()) != FALSE && ! $parent_class->isInternal()) {
            $this->parent_class = CyDocs_Model::for_reflector($parent_class);
        }
        $this->is_final = $reflector->isFinal();
        $this->is_abstract = $reflector->isAbstract();
        $this->is_interface = $reflector->isInterface();
        $this->comment = $reflector->getDocComment();
        $this->reflector = $reflector;

        $exclude_private = ! CyDocs::inst()->internal;
        foreach ($reflector->getInterfaces() as $intf) {
            if ( ! $intf->isInternal()) {
                $this->implemented_interfaces []= CyDocs_Model::for_reflector($intf);
            }
        }
        foreach ($reflector->getConstants() as $name => $value) {
            $this->constants[$name] = $value;
        }
        foreach ($reflector->getStaticProperties() as $ref_prop) {
            //$this->static_properties []= CyDocs_Model::for_reflector($ref_prop);
        }
        foreach ($reflector->getProperties() as $ref_prop) {
            if ($ref_prop->getDeclaringClass() == $reflector
                    && ! ($exclude_private && $ref_prop->isPrivate())) {
                $this->properties []= CyDocs_Model::for_reflector($ref_prop);
            }
        }
        foreach ($reflector->getMethods() as $ref_method) {
            if ($ref_method->getDeclaringClass() == $reflector
                    && ! ($exclude_private && $ref_method->isPrivate())) {
                $this->methods []= CyDocs_Model::for_reflector($ref_method);
            }
        }
        CyDocs::inst()->current_class = NULL;
    }

    public function  post_loading() {
        CyDocs::inst()->current_class = $this->name;
        $parser = new CyDocs_Parser($this->comment, $this);
        $comment = $this->comment = $parser->parse();
        //var_dump($comment->annotations);
        $prop_annots = $comment->annotations_by_name(array('property', 'property-read'));
        foreach ($prop_annots as $prop_annot) {
            $prop = new CyDocs_Model_Property;
            $prop->name = $prop_annot->formal_name;
            $prop->type = $prop_annot->type;
            $this->properties []= $prop;
        }
        $pkg_annots = $comment->annotations_by_name('package');
        switch (count($pkg_annots)) {
            case 0:
                log_warning($this, 'couldn\'t determine library for class ' . $this->name);
                break;
            case 1:
                $this->library = strtolower($pkg_annots[0]->text);
                CyDocs_Model_Library::add_class($this);
                break;
            default:
                log_warning($this, 'multiple @package annotations for class ' . $this->name);
        }
        foreach (self::$_classes as $class) {
            if ($class->parent_class === $this) {
                $this->subclasses []= $class;
            }
        }

        foreach ($this->properties as $model) {
            $model->post_loading();
        }
        foreach ($this->methods as $model) {
            $model->post_loading();
        }
        CyDocs::inst()->current_class = NULL;
    }

    public function  string_identifier() {
        return $this->name;
    }

}