<?php

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Method extends CyDocs_Model {

    /**
     * Flag marking that the represented method is static or not.
     *
     * @var boolean
     */
    public $is_static;

    /**
     * The class that  this method belongs to.
     *
     * @var CyDocs_Model_Class
     */
    public $class;

    /**
     * Flag marking that the represented method is static or not.
     *
     * @var boolean
     */
    public $is_abstract;

    /**
     * Flag marking that the represented class is final or not.
     *
     * @var boolean
     */
    public $is_final;

    /**
     * The visibility of the method. See \c CyDocs_Model constants.
     *
     * @var string
     */
    public $visibility;

    /**
     * Flag marking that the method is the constructor of the class.
     *
     * @var boolean
     */
    public $is_constructor;

    /**
     * Flag marking that the method is the constructor of the class.
     *
     * @var boolean
     */
    public $is_destructor;

    /**
     * The declared parameters of the represented method.
     *
     * @var array<CyDocs_Model_Parameter>
     */
    public $parameters = array();

    /**
     * The return type of the method.
     *
     * @var string
     */
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
        parent::post_loading();
        $parser = new CyDocs_Parser($this->comment, $this); 
        $comment = $parser->parse();
        $return_annots = $comment->annotations_by_name(array('return', 'returns'));
        if (count($return_annots) > 1) {
            log_warning($this, 'ambigious return type for ' . $this->string_identifier());
            $this->return_type = $return_annots[0]->type;
        } elseif (count($return_annots) == 1) {
            $this->return_type = $return_annots[0]->type;
        } else {
            $this->return_type = 'void';
        }

        $this->return_type = CyDocs_Model::coderef_to_anchor($this->return_type);

        $param_annots = $comment->annotations_by_name('param');
        foreach ($param_annots as $param_annot) {
            if ( ! $param_annot->formal_name) {
                log_warning($this, 'invalid @param annotation at ' . $this->string_identifier());
                continue;
            }
            $found_param_model = NULL;
            foreach ($this->parameters as $param) {
                if ('$' . $param->name == $param_annot->formal_name) {
                    $found_param_model = $param;
                    break;
                }
            }
            if (NULL === $found_param_model) {
                log_warning($this, $this->string_identifier() . ' does not have parameter \''
                        . $param_annot->formal_name . '\'');
                continue;
            }
            $found_param_model->type = $param_annot->type;
        }
        foreach ($this->parameters as $model) {
            $model->post_loading();
        }
    }

    public function  string_identifier() {
        return $this->class->name . '::' . $this->name . '()';
    }

}
