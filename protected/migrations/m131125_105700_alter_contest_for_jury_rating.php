<?php

class m130906_050040_alter_contest_table extends CDbMigration {

  public function up() {
    $this->execute("ALTER TABLE `contest` ADD `jury_rating_from` DATETIME NOT NULL ,
      ADD `jury_rating_till` DATETIME NOT NULL");
  }

  public function down() {
    $this->execute("ALTER TABLE `contest` DROP `jury_rating_from`");   
    $this->execute("ALTER TABLE `contest` DROP `jury_rating_till`");   
  }
} 