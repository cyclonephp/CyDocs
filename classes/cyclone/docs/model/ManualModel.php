<?php

namespace cyclone\docs\model;

use cyclone as cy;

/**
 * Represents a library manual created by \c CyDocs_Text_Formatter::create_manual()
 *
 * The HTML output for the manual can be obtained as a string using \c CyDocs_Model_Manual::render()
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class ManualModel {

    /**
     * @var string
     */
    public $title;

    /**
     * @var array<CyDocs_Model_Manual_Section>
     */
    public $sections = array();

    /**
     * @var string
     */
    public $text;

    public function render() {
        return cy\view\PHPView::factory('cydocs/manual', array(
            'manual' => $this
        ))->render();
    }
    
}