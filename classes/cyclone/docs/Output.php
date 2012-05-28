<?php

namespace cyclone\docs;

/**
 * 
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
interface Output {

    public function generate_api();

    public function generate_manual();
    
}
