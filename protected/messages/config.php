<?php

return array(
    'sourcePath' => dirname(__FILE__) . '/../../protected',
    'messagePath' => dirname(__FILE__) . '/../messages',
    'languages' => array('it_it'),
    'translator' => 't',
    'launchpad' => false,
    'overwrite' => true,
    'autoMerge' => true,
    'removeOld' => true,
    'exclude' => array(
        '.git',
        '.svn',
        '/framework',
        '/vendors/',
        '/migrations/',
        '/runtime/',
        '/extensions/',
        '/messages/',
    ),
);