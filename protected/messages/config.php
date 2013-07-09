<?php

return array(
    'sourcePath' => dirname(__FILE__) . '/../../protected',
    'messagePath' => dirname(__FILE__) . '/../messages',
    'translator' => 'Yii::t',
    'languages' => array('it_it'),
    'fileTypes' => array('php'),
    'launchpad' => false,
    'overwrite' => true,
    'autoMerge' => true,
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