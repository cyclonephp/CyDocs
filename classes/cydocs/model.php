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
            $key = $reflector->getDeclaringClass()->getName() . '::$' . $reflector->getName();
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
        static $gen_arr_prefix = 'array<';
        $gen_arr_prefix_len = strlen($gen_arr_prefix);
        $coderef_str = trim($coderef_str);
        // checking if it is an array<type> code reference.
        if (substr($coderef_str, 0, $gen_arr_prefix_len) == $gen_arr_prefix // statrs with 'array<'
                && $coderef_str{strlen($coderef_str) - 1} == '>') { // ends with '>'
            $generic_param = substr($coderef_str, $gen_arr_prefix_len
                    , strlen($coderef_str) - 1 - $gen_arr_prefix_len);
            return 'array&lt;' . self::coderef_to_anchor($generic_param) . '&gt;';
        }
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

        $candidate_prop_key = $classname . '::$' . $toolname;

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
        } elseif (isset(self::$_properties[$candidate_prop_key])) {
            return '<a class="coderef" href="' . $root_path
                . CyDocs_Output_HTML_Library::class_docs_file($classname)
                . '#prop-' . $toolname
                . '">'
                . $coderef_str . '</a>';
        }
        

        return $coderef_str;
    }

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
     * The <code>uses</code> annotations found in the model comment.
     * The annotation can be easily rendered, since \c CyDocs_Model_Annotation_Link::init()
     * has already created the HTML <code>&lt;a&gt;</code> tag, and resolved
     * the code reference (if any).
     *
     * @var array<CyDocs_Model_Annotation_Link>
     */
    public $uses = array();

    /**
     * The <code>usedby</code> annotations found in the model comment.
     * The annotation can be easily rendered, since \c CyDocs_Model_Annotation_Link::init()
     * has already created the HTML <code>&lt;a&gt;</code> tag, and resolved
     * the code reference (if any).
     *
     * @var array<CyDocs_Model_Annotation_Link>
     * @usedby CyDocs_Model::process_links()
     */
    public $usedby = array();

    /**
     * The <code>see</code> and <code>link</code> annotations found in the model comment.
     * The annotation can be easily rendered, since \c CyDocs_Model_Annotation_Link::init()
     * has already created the HTML <code>&lt;a&gt;</code> tag, and resolved
     * the code reference (if any).
     *
     * @var array<CyDocs_Model_Annotation_Link>
     */
    public $link = array();


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

    /**
     * Populates the following properties:
     * <ul>
     *  <li>uses</li>
     *  <li>usedby</li>
     *  <li>link</li>
     *  <li>see</li>
     * </ul>
     *
     * This method is recommended to be called from the \c post_loading()
     * implementation of the subclasses.
     * @uses CyDocs_Model::$uses
     */
    protected function process_links() {
        if ( ! ($this->comment instanceof CyDocs_Model_Comment)) {
        $parser = new CyDocs_Parser($this->comment, $this);
        $comment = $parser->parse();
        } else {
            $comment = $this->comment;
        }
        $uses_annotations = $comment->annotations_by_name('uses');
        foreach ($uses_annotations as $uses_ann) {
            $this->uses []= $uses_ann;
        }
        $usedby_annotations = $comment->annotations_by_name('usedby');
        foreach ($usedby_annotations as $usedby_ann) {
            $this->usedby []= $usedby_ann;
        }
        $link_annots = $comment->annotations_by_name(array('link', 'see'));
        foreach ($link_annots as $link_annot) {
            $this->link []= $link_annot;
        }

    }

}
