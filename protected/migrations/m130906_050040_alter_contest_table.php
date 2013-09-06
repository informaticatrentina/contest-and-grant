<?php

class m130906_050040_alter_contest_table extends CDbMigration {

  public function up() {
    $this->execute("ALTER TABLE `contest` ADD `winnerStatus` TINYINT( 3 ) NOT NULL");
  }

  public function down() {
    $this->execute("ALTER TABLE `contest` DROP `winnerStatus`");   
  }
}