<?php

class m131217_135449_alter_contest_table_for_intro extends CDbMigration {

  public function up() {
    $this->execute("ALTER TABLE `contest` ADD `intro_title` VARCHAR( 100 ) NULL ,
      ADD `intro_description` TEXT NULL ,
      ADD `intro_status` TINYINT( 3 ) NOT NULL DEFAULT '0'");
  }

  public function down() {
    $this->execute('ALTER TABLE `contest` DROP `intro_title`');
    $this->execute('ALTER TABLE `contest` DROP `intro_description`');
    $this->execute('ALTER TABLE `contest` DROP `intro_status`');
  }
}