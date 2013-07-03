<?php

return array(
    'sourcePath' => dirname(__FILE__) . '/../..',
    'messagePath' => dirname(__FILE__) . '/../messages',
    'translator' => 'Yii::t',
    'languages' => array('it_it, en_us'),
    'fileTypes' => array('php, js, po'),
    'launchpad' => false,
    'overwrite' => true,
    'exclude' => array(
        '.git',
        '.svn',
        '/framework',
        '/protected',
    ),
);