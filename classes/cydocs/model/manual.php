<?php

/**
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Manual {

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
        return View::factory('cydocs/manual', array(
            'manual' => $this
        ))->render();
    }
    
}