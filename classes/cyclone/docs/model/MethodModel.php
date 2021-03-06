<?php

namespace cyclone\docs\model;

use cyclone\docs;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class MethodModel extends AbstractModel {

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
     * The visibility of the method. See @c CyDocs_Model constants.
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
     * @var array<ParameterModel>
     */
    public $parameters = array();

    /**
     * The return type of the method.
     *
     * @var string
     */
    public $return_type;

    /**
     * The exceptions thrown by the method. Exception class => description pairs
     *
     * @var array
     */
    public $thrown_exceptions = array();

    /**
     * The parsed comment of the method.
     *
     * @var CyDocs_Model_Comment
     */
    private $_comment;
    

    public function init() {
        $reflector = $this->reflector;
        $this->is_static = $reflector->isStatic();
        $this->is_abstract = $reflector->isAbstract();
        $this->class = AbstractModel::for_reflector($reflector->getDeclaringClass());
        $this->name = $reflector->getName();
        $this->comment = $reflector->getDocComment();
        if ($reflector->isPublic()) {
            $this->visibility = AbstractModel::VISIBILITY_PUBLIC;
        }
        if ($reflector->isProtected()) {
            $this->visibility = AbstractModel::VISIBILITY_PROTECTED;
        }
        if ($reflector->isPrivate()) {
            $this->visibility = AbstractModel::VISIBILITY_PRIVATE;
        }

        $this->is_constructor = $reflector->isConstructor();
        $this->is_destructor = $reflector->isDestructor();

        foreach ($reflector->getParameters() as $ref_param) {
            $this->parameters []= AbstractModel::for_reflector($ref_param);
        }
    }

    public function post_loading() {
        parent::post_loading();
        $parser = new docs\Parser($this->comment, $this);
        $this->_comment = $comment = $parser->parse();
        $return_annots = $comment->annotations_by_name(array('return', 'returns'));
        if (count($return_annots) > 1) {
            log_warning($this, 'ambigious return type for ' . $this->string_identifier());
            $this->return_type = $return_annots[0]->type;
        } elseif (count($return_annots) == 1) {
            $this->return_type = $return_annots[0]->type;
        } else {
            $this->return_type = 'void';
        }

        $this->return_type = AbstractModel::coderef_to_anchor($this->return_type);

        $this->process_param_annots();
        $this->process_throws_annots();
        
        foreach ($this->parameters as $model) {
            $model->post_loading();
        }
        parent::process_links();
    }

    public function get_param_annot_idx(annotation\ParamAnnotation $annot) {
        $idx = 0;
        $param_annots = $this->_comment->annotations_by_name('param');
        foreach ($param_annots as $p_annot) {
            if ($p_annot === $annot)
                return $idx;
            ++$idx;
        }
        throw new docs\Exception("$annot does not seem to be an annotation of " . $this->string_identifier());
    }

    private function process_param_annots() {
        $param_annots = $this->_comment->annotations_by_name('param');
        $i = 0;
        foreach ($param_annots as $param_annot) {
            if ( ! $param_annot->formal_name) {
                $ref_params = $this->reflector->getParameters();
                if ( ! isset($ref_params[$i])) {
                    log_warning($this, $this->string_identifier() . ' has ' . count($ref_params) . 'parameters, but ' .count($param_annots) . ' @param annotations found');
                } else {
                    $param_annot->formal_name = $ref_params[$i]->getName();
                }
            }

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
            ++$i;
        }
    }

    private function process_throws_annots() {
        $throws_annots = $this->_comment->annotations_by_name('throws');
        foreach ($throws_annots as $throw_annot) {
            if ( ! $throw_annot->exception_class)
                continue;

            $exc_coderef = AbstractModel::coderef_to_anchor($throw_annot->exception_class);
            $this->thrown_exceptions[$exc_coderef] = $throw_annot->text;
        }
    }

    public function  string_identifier() {
        return $this->class->name . '::' . $this->name . '()';
    }

    public function modifiers() {
        $rval = $this->visibility;
        if ($this->is_static) {
            $rval .= ' static';
        }
        if ($this->is_abstract) {
            $rval .= ' abstract';
        }
        if ($this->is_final) {
            $rval .= ' final';
        }
        return $rval;
    }

}
