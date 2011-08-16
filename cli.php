<?php

return array(
    'docs' => array(
        'descr' => 'Documentation generator tool for CyclonePHP',
        'commands' => array(
            'api' => array(
                'descr' => 'generates API docs for the classes in the enables libraries',
                'callback' => array(CyDocs::inst(), 'cli_api_bootstrap'),
                'arguments' => array(
                    '--root-dir' => array(
                        'alias' => '-r',
                        'default' => DOCROOT . 'docs/',
                        'parameter' => '<root-dir>',
                        'descr' => 'the root directory of the generated documentation'
                    ),
                    '--internal' => array(
                        'alias' => '-i',
                        'default' => FALSE,
                        'parameter' => NULL,
                        'descr' => 'include internal documentation too (recommmended for people who develop CyclonePHP itself)'
                    ),
                    '--lib' => array(
                        'parameter' => '<libraries>',
                        'alias' => '-l',
                        'descr' => 'a comma-separated list of libraries that\'s docs should be generated. all stands for all libs (including the application)',
                        'default' => 'all'
                    ),
                    '--stylesheet' => array(
                        'parameter' => '<path>',
                        'alias' => '-s',
                        'descr' => 'the stylesheet file that should be applied to the output',
                        'default' => FileSystem::get_root_path('cydocs') . 'assets/css/default.css'
                    ),
                    '--pdf' => array(
                        'alias' => '-p',
                        'default' => FALSE,
                        'descr' => 'flag marking that PDF output should be generated instead of HTML',
                        'parameter' => NULL
                    ),
                    '--forced' => array(
                        'parameter' => NULL,
                        'default' => FALSE,
                        'alias' => '-f',
                        'descr' => 'the generator removes all existing docs directories if passed'
                    )
                )
            )
        )
    )
);