<?php

abstract class CyDocs_Model {

    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PROTECTED = 'protected';

    const VISIBILITY_PRIVATE = 'private';

    public $name;

    public $reflector;

    public $comment;

    protected static $_classes = array();

    protected static $_methods = array();

    protected static $_properties = array();

    protected static $_parameters = array();

    protected function  __construct(Reflector $reflector) {
        $this->reflector = $reflector;
    }

    /**
     * Factory/object pool method for CyDocs_Model subclasses.
     *
     * @param Reflector $reflector
     * @return CyDocs_Model
     */
    public static function for_reflector(Reflector $reflector) {
        if ($reflector instanceof ReflectionClass) {
            $key = $reflector->getName();
            if (isset(self::$_classes[$key]))
                return self::$_classes[$key];
            self::$_classes[$key] = new CyDocs_Model_Class($reflector);
            self::$_classes[$key]->init();
            return self::$_classes[$key];
        }
        if ($reflector instanceof ReflectionMethod) {
            $key = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
            if (isset(self::$_methods[$key]))
                return self::$_methods[$key];
            self::$_methods[$key] = new CyDocs_Model_Method($reflector);
            self::$_methods[$key]->init();
            return self::$_methods[$key];
        }
        if ($reflector instanceof ReflectionProperty) {
            $key = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
            //echo count(self::$_properties) . '---------------------' .$key. PHP_EOL;
            if (isset(self::$_properties[$key]))
                return self::$_properties[$key];
            self::$_properties[$key] = new CyDocs_Model_Property($reflector);
            self::$_properties[$key]->init();
            return self::$_properties[$key];
        }
        if ($reflector instanceof ReflectionParameter) {
            $method_key = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
            if ( ! isset(self::$_parameters[$method_key])) {
                self::$_parameters[$method_key] = array();
            }
            if (isset(self::$_parameters[$method_key][$reflector->getName()]))
                return self::$_parameters[$method_key][$reflector->getName()];
            self::$_parameters[$method_key][$reflector->getName()] = new CyDocs_Model_Parameter($reflector);
            self::$_parameters[$method_key][$reflector->getName()]->init();
            return self::$_parameters[$method_key][$reflector->getName()];
        }
        throw new CyDocs_Exception('no CyDocs_Model implementation for ' . get_class($reflector));
    }

    public static function fire_post_load() {
        foreach (self::$_classes as $model) {
            $model->post_loading();
        }
        foreach (self::$_properties as $model) {
            $model->post_loading();
        }
        foreach (self::$_methods as $model) {
            $model->post_loading();
        }
        foreach (self::$_parameters as $method_params) {
            foreach ($method_params as $model) {
                $model->post_loading();
            }
        }
    }

    abstract function init();

    /**
     * Called by CyDocs_Model for each instance after a CyDocs_Model_Class instance
     * is created for all PHP classes to be documented.
     */
    abstract function post_loading();
}