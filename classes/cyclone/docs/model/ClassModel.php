<?php

namespace cyclone\docs\model;

use cyclone as cy;
use cyclone\docs;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class ClassModel extends AbstractModel {

    /**
     * The library that the represented class belongs to.
     *
     * @var CyDocs_Model_Library
     */
    public $library;

    /**
     * The direct superclass of the represented class.
     *
     * @var CyDocs_Model_Class
     */
    public $parent_class;

    /**
     * The direct known subclasses of the represented class.
     *
     * @var array<CyDocs_Model_Class>
     */
    public $subclasses = array();

    /**
     * The interfaces implemented by the represented class.
     *
     * @var array<CyDocs_Model_Class>
     */
    public $implemented_interfaces = array();

    /**
     * Class constanst (key-value pairs).
     *
     * @var array
     */
    public $constants = array();

    /**
     *
     * @var array
     */
    public $static_properties;

    /**
     * The declared properties of the class.
     *
     * @var array<CyDocs_Model_Property>
     */
    public $properties = array();

    /**
     * The methods of the represented class.
     *
     * @var array<CyDocs_Model_Method>
     */
    public $methods = array();

    /**
     * Flag marking that the class is final or not.
     *
     * @var boolean
     */
    public $is_final;

    /**
     * Flag marking that the class is abstract or not.
     *
     * @var boolean
     */
    public $is_abstract;

    /**
     * Flag marking that the represented class is in fact not a class but an interface.
     *
     * @var boolean
     */
    public $is_interface;

    public function init() {
        $reflector = $this->reflector;
        cy\Docs::inst()->current_class = $this->name = $reflector->getName();
        if (($parent_class = $reflector->getParentClass()) != FALSE && ! $parent_class->isInternal()) {
            $this->parent_class = AbstractModel::for_reflector($parent_class);
        }
        $this->is_final = $reflector->isFinal();
        $this->is_abstract = $reflector->isAbstract();
        $this->is_interface = $reflector->isInterface();
        $this->comment = $reflector->getDocComment();
        $this->reflector = $reflector;

        $exclude_private = ! cy\Docs::inst()->internal;
        foreach ($reflector->getInterfaces() as $intf) {
            if ( ! $intf->isInternal()) {
                $this->implemented_interfaces []= AbstractModel::for_reflector($intf);
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
                $this->properties []= AbstractModel::for_reflector($ref_prop);
            }
        }
        foreach ($reflector->getMethods() as $ref_method) {
            if ($ref_method->getDeclaringClass() == $reflector
                    && ! ($exclude_private && $ref_method->isPrivate())) {
                $this->methods []= AbstractModel::for_reflector($ref_method);
            }
        }
        cy\Docs::inst()->current_class = NULL;
    }

    private function create_pseudo_properties($prop_annots) {
        foreach ($prop_annots as $prop_annot) {
            $prop_name = $prop_annot->formal_name;
            if ($prop_name{0} == '$') {
                $prop_name = substr($prop_name, 1);
            }
            $existing_prop = NULL;
            foreach ($this->properties as $prop_model) {
                if ($prop_model->name{0} != '_')
                    continue;

                $existing_prop_name = substr($prop_model->name, 1);
                if ($existing_prop_name == $prop_name) {
                    $existing_prop = $prop_model;
                    break;
                }
            }
            if ($prop_annot->name == 'property-read') {
                $new_visibility = AbstractModel::VISIBILITY_READONLY;
            } else {
                $new_visibility = AbstractModel::VISIBILITY_PUBLIC;
            }
            if ($existing_prop === NULL) {
                $prop = new PropertyModel;
                $prop->name = $prop_annot->formal_name;
                $prop->type = $prop_annot->type;
                $prop->class = $this;
                $prop->visibility = $new_visibility;
                $this->properties [] = $prop;
                $obj_pool_key = $this->name . '::' . $prop->name;
                self::$_properties[$obj_pool_key] = $prop;
            } else {
                $existing_prop->visibility = $new_visibility;
                $old_pool_key = $this->name . '::' . $existing_prop->name;
                $existing_prop->name = substr($existing_prop->name, 1);
                $new_pool_key = $this->name . '::' . $existing_prop->name;
                unset(self::$_properties[$old_pool_key]);
                self::$_properties[$new_pool_key] = $existing_prop;
            }
        }
    }

    public function  post_loading() {
        parent::post_loading();
        cy\Docs::inst()->current_class = $this->name;
        $parser = new docs\Parser($this->comment, $this);
        $comment = $this->comment = $parser->parse();
        $prop_annots = $comment->annotations_by_name(array('property', 'property-read'));
        $this->create_pseudo_properties($prop_annots);
        $pkg_annots = $comment->annotations_by_name('package');
        switch (count($pkg_annots)) {
            case 0:
                log_warning($this, 'couldn\'t determine library for class ' . $this->name);
                break;
            case 1:
                $this->library = strtolower($pkg_annots[0]->text);
                LibraryModel::add_class($this);
                break;
            default:
                log_warning($this, 'multiple @package annotations for class ' . $this->name);
        }
        foreach (self::$_classes as $class) {
            if ($class->parent_class === $this) {
                $this->subclasses []= AbstractModel::coderef_to_anchor($class->name);
            }
        }

        foreach ($this->properties as $model) {
            //echo get_class($model) . "\n";
            //echo "post-loading " . $model->string_identifier() . "\n";
            $model->post_loading();
        }
        
        foreach ($this->methods as $model) {
            $model->post_loading();
        }
        /*if ( ! is_null($this->parent_class)) {
            $this->parent_class = AbstractModel::coderef_to_anchor($this->parent_class->name);
        }*/
        uasort($this->subclasses, function($a, $b) {
            return strcmp($a, $b);
        });
        uasort($this->properties, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
        uasort($this->methods, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
        parent::process_links();
        cy\Docs::inst()->current_class = NULL;
    }

    public function  string_identifier() {
        return $this->name;
    }

    public function modifiers() {
        $modifiers = '';
        if ($this->is_final) {
            $modifiers .= 'final ';
        }
        if ($this->is_abstract && ! $this->is_interface) {
            $modifiers .= 'abstract ';
        }
        if ($this->is_interface) {
            $modifiers .= 'interface';
        } else {
            $modifiers .= 'class';
        }
        $modifiers .= ' ';
        return $modifiers;
    }

}
