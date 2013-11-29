<?php

class m131125_105700_alter_contest_for_jury_rating extends CDbMigration {

  public function up() {
    $this->execute("ALTER TABLE `contest` ADD `jury_rating_from` DATETIME NOT NULL ,
      ADD `jury_rating_till` DATETIME NOT NULL");
  }

  public function down() {
    $this->execute("ALTER TABLE `contest` DROP `jury_rating_from`");   
    $this->execute("ALTER TABLE `contest` DROP `jury_rating_till`");   
  }
} 