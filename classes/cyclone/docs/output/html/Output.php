<?php

namespace cyclone\docs\output\html;

use cyclone\docs;
use cyclone as cy;


/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
class Output implements docs\Output {

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

    public function generate_api() {
        mkdir($this->_root_dir . 'libs/');
        $index_view = cy\view\PHPView::factory('cydocs/index');
        file_put_contents($this->_root_dir . 'index.html', $index_view->render());
        copy($this->_stylesheet, $this->_root_dir . 'stylesheet.css');

        $this->create_libs_html();

        foreach ($this->_lib_models as $model) {
            $libroot = $this->_root_dir . 'libs/' . $model->name . '/';
            mkdir($libroot);
            $lib_output = new LibraryOutput(
                $libroot, $model, $this->_stylesheet);
            $lib_output->generate_api();
            $lib_output->generate_manual();
            $this->_lib_outputs []= $lib_output;
        }
    }

    public function create_libs_html() {
        $libs_data = array();
        foreach ($this->_lib_models as $lib_model) {
            $libs_data[$lib_model->name] = './libs/' . $lib_model->name . '/classes.html';
        }
        $liblist_view = cy\view\PHPView::factory('cydocs/libs'
                , array('libs' => $libs_data));
        file_put_contents($this->_root_dir . 'libs.html', $liblist_view->render());
    }

    public function  generate_manual() {
        $lib_manuals = array();
        cy\Docs::inst()->current_class = NULL;
        foreach ($this->_lib_models as $model) {
            $lib_root_path = cy\FileSystem::get_root_path($model->name);
            $manual_file = $lib_root_path . 'manual/manual.txt';
            if (file_exists($manual_file)) {
                $lib_manuals[$model->name] = docs\Formatter::manual_formatter(file_get_contents($manual_file))
                        ->create_manual();
                $lib_manuals[$model->name]->title = $model->name;
            } else {
                log_warning($this, "no manual found for library '{$model->name}'");
            }
        }
        file_put_contents($this->_root_dir . 'manual.html'
                , $this->merge_lib_manuals($lib_manuals)->render());
    }

    public function merge_lib_manuals(array $lib_manuals) {
        $rval = new docs\model\ManualModel;
        foreach ($lib_manuals as $lib_manual) {
            $lib_section = new docs\model\SectionModel;
            $lib_section->id = $lib_manual->title;
            $lib_section->title = $lib_manual->title;
            $lib_section->sections = $lib_manual->sections;
            $lib_section->text = $lib_manual->text;
            $rval->sections []= $lib_section;
        }
        if (cy\Docs::inst()->preface !== FALSE) {
            $preface = file_get_contents(cy\Docs::inst()->preface);
            $rval->text = $preface . $rval->text;
        }
        $rval->title = cy\Docs::inst()->title;
        return $rval;
    }

}
