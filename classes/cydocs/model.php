<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
abstract class CyDocs_Model {

    const VISIBILITY_PUBLIC = 'public';

    const VISIBILITY_PROTECTED = 'protected';

    const VISIBILITY_PRIVATE = 'private';

    const VISIBILITY_READONLY = 'readonly';

    /**
     * The name of the represented tool (a class name,  property name,
     * method name or method parameter name).
     *
     * @var string
     */
    public $name;

    /**
     * The reflector instance that is examined. The properties of the subclasses
     * will be populated using this property by the \c init() implementations.
     *
     * @var Reflector
     */
    public $reflector;

    /**
     * The raw text of the comment.
     *
     * @var string
     */
    public $comment;

    /**
     * The already HTML-formatted free-form text
     *
     * @var string
     */
    public $free_form_text;

    /**
     * Object pool for the created class instances.
     * It's maintained by \c for_reflector() .
     *
     * @var array<CyDocs_Model_Class>
     */
    protected static $_classes = array();

    /**
     * Object pool for the created method instances.
     * It's maintained by \c for_reflector() .
     *
     * @var array<CyDocs_Model_Method>
     */
    protected static $_methods = array();

    /**
     * Object pool for the created property instances.
     * It's maintained by \c for_reflector() .
     *
     * @var array<CyDocs_Model_Class>
     */
    protected static $_properties = array();

    /**
     * Object pool for the created parameter instances.
     * It's maintained by \c for_reflector() .
     *
     * @var array<CyDocs_Model_Class>
     */
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
            $key = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName() . '()';
            if (isset(self::$_methods[$key]))
                return self::$_methods[$key];
            self::$_methods[$key] = new CyDocs_Model_Method($reflector);
            self::$_methods[$key]->init();
            return self::$_methods[$key];
        }
        if ($reflector instanceof ReflectionProperty) {
            $key = $reflector->getDeclaringClass()->getName() . '::' . $reflector->getName();
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
    }

    public static function coderef_to_anchor($coderef_str) {
        $root_path = CyDocs_Output_HTML_Library::path_to_root(CyDocs::inst()->current_class);
        $coderef = explode('::', $coderef_str);
        if (count($coderef) == 1) {
            $classname = CyDocs::inst()->current_class;
            $toolname = $coderef[0];
            // the tool name can be a class name
            if (isset(self::$_classes[$toolname])) {
                return '<a class="coderef" href="' . $root_path
                . CyDocs_Output_HTML_Library::class_docs_file($toolname) . '">'
                . $coderef_str . '</a>';
            }
        } elseif (count($coderef) == 2) {
            $classname = $coderef[0];
            $toolname = $coderef[1];
        } else {
            log_error($this, "invalid code reference: $coderef");
            return $coderef_str;
        }

        $candidate_key = $classname . '::' . $toolname;

        if (isset(self::$_methods[$candidate_key])) {
            return '<a class="coderef" href="' . $root_path
                . CyDocs_Output_HTML_Library::class_docs_file($classname)
                . '#method-' . $toolname
                . '">' . $coderef_str . '</a>';
        } elseif (isset(self::$_properties[$candidate_key])) {
            return '<a class="coderef" href="' . $root_path
                . CyDocs_Output_HTML_Library::class_docs_file($classname)
                . '#prop-' . $toolname
                . '">'
                . $coderef_str . '</a>';
        }
        

        return $coderef_str;
    }

    public abstract function init();

    public abstract function string_identifier();

    /**
     * Called by CyDocs_Model for each instance after a CyDocs_Model_Class instance
     * is created for all PHP classes to be documented.
     */
    public function post_loading() {
        if (NULL === $this->comment)
            return;

        $parser = new CyDocs_Parser($this->comment, $this);
        $comment = $parser->parse();

        $this->free_form_text = CyDocs_Text_Formatter::comment_formatter($comment->text)
                ->format();
    }

}
