<?php


/**
 * 
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Output_HTML implements CyDocs_Output {

    /**
     * The absolute path of the root directory of the generated documentation.
     *
     * @var string
     */
    private $_root_dir;

    /**
     * The libraries thats documentation is generated.
     *
     * @var array<CyDocs_Model_Library>
     */
    private $_lib_models;

    /**
     * The library output generators.
     *
     * @var array<CyDocs_Output_HTML_Library>
     */
    private $_lib_outputs = array();

    /**
     * The stylesheet to be applied on the output.
     *
     * @var string
     */
    private $_stylesheet;

    /**
     *
     * @param string $root_dir
     * @param array $lib_models
     */
    public function  __construct($root_dir, $lib_models, $stylesheet) {
        $this->_root_dir = $root_dir;
        $this->_lib_models = $lib_models;
        $this->_stylesheet = $stylesheet;
    }

    public function generate() {
        mkdir($this->_root_dir . 'libs/');
        foreach ($this->_lib_models as $model) {
            mkdir ($this->_root_dir . 'libs/' . $model->name . '/');
            $lib_output = new CyDocs_Output_HTML_Library(
                $this->_root_dir . 'libs/' . $model->name . '/'
                , $model, $this->_stylesheet);
            $lib_output->generate();
            $this->_lib_outputs []= $lib_output;
        }
    }

}
