<?php

class m130604_145037_alter_table_contest extends CDbMigration {

  public function up() {
    $this->execute(
            'ALTER TABLE `contest` ADD `squareImage` VARCHAR( 100 ) NOT NULL AFTER `imagePath`'
    );
  }

  public function down() {
    $this->execute('ALTER TABLE `contest` DROP `squareImage`');
  }

}