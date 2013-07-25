<?php

return array(
    'sourcePath' => dirname(__FILE__) . '/../../js',
    'messagePath' => dirname(__FILE__) . '/../messages',
    'translator' => 'Yii.t',
    'languages' => array('it_it'),
    'fileTypes' => array('js'),
    'overwrite' => true,
    'exclude' => array(
        '.git',
        '.svn',
        '/framework',
        '/protected',
    ),
);