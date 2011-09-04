<?php

/**
 *
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyDocs
 */
class CyDocs_Model_Manual_Section {

    /**
     *
     * @var string
     */
    public $id;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var array<CyDocs_Model_Manual_Section>
     */
    public $sections = array();

    /**
     *
     * @var string
     */
    public $text;

    public function render() {
        return View::factory('cydocs/manual/section', array(
            'section' => $this,
            'level' => 1
        ))->render();
    }
}