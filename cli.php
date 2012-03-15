<?php

use cyclone as cy;

return array(
    'docs' => array(
        'descr' => 'Documentation generator tool for CyclonePHP',
        'commands' => array(
            'api' => array(
                'descr' => 'generates API docs for the classes in the enables libraries',
                'callback' => array(cy\Docs::inst(), 'cli_api_bootstrap'),
                'arguments' => array(
                    '--output-dir' => array(
                        'alias' => '-o',
                        'default' => cy\SYSROOT . 'docs/',
                        'parameter' => '<output-dir>',
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
                        'default' => cy\FileSystem::get_root_path('cydocs') . 'assets/css/cydocs/default.css'
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
                    ),
                    '--measure' => array(
                        'parameter' => NULL,
                        'default' => FALSE,
                        'alias' => '-m',
                        'descr' => 'flag marking if the execution time and memory usage'
                    ),
                    '--title' => array(
                        'parameter' => '<title>',
                        'alias' => '-t',
                        'default' => '',
                        'descr' => 'The title of the generated documentation. Only used if the generation goes for multiple libraries.'
                    ),
                    '--preface' => array(
                        'parameter' => '<html-file-containing-preface>',
                        'alias' => '-p',
                        'default' => FALSE,
                        'descr' => 'The main page of the output. Only used if the generation goes for multiple libraries.'
                    ),
                    '--line-numbers' => array(
                        'parameter' => NULL,
                        'alias' => '-L',
                        'descr' => 'show line numbers of code examples'
                    )
                )
            )
        )
    )
);