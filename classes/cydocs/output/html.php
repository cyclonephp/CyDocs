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
        $index_view = View::factory('cydocs/index');
        file_put_contents($this->_root_dir . 'index.html', $index_view->render());
        copy($this->_stylesheet, $this->_root_dir . 'stylesheet.css');

        $this->create_libs_html();

        foreach ($this->_lib_models as $model) {
            $libroot = $this->_root_dir . 'libs/' . $model->name . '/';
            mkdir($libroot);
            $lib_output = new CyDocs_Output_HTML_Library(
                $libroot, $model, $this->_stylesheet);
            $lib_output->generate();
            $this->_lib_outputs []= $lib_output;
        }
    }

    public function create_libs_html() {
        $libs_data = array();
        foreach ($this->_lib_models as $lib_model) {
            $libs_data[$lib_model->name] = $this->_root_dir . 'libs/' . $lib_model->name . '/classes.html';
        }
        $liblist_view = View::factory('cydocs/libs'
                , array('libs' => $libs_data));
        file_put_contents($this->_root_dir . 'libs.html', $liblist_view->render());
    }

}
