<?php
namespace cyclone\docs;

/**
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package CyDocs
 */
interface RootPathProvider {

    public function path_to_root($classname = NULL);

}
