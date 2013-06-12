<?php

class m130612_065324_add_entryStatus extends CDbMigration {

  public function up() {
    $this->execute('ALTER TABLE `contest` ADD `entryStatus` TINYINT( 2 ) NOT NULL' );
  }

  public function down() {
    $this->execute(' ALTER TABLE `contest` DROP `entryStatus`');
  }
}