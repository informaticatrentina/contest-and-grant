<?php

class m130605_105632_alter_contest_table_add_regolomento extends CDbMigration {

  public function up() {
    $this->execute('ALTER TABLE `contest` ADD `rule` TEXT NOT NULL');
  }

  public function down() {
    $this->execute('ALTER TABLE `contest` DROP `rule`');
  }

}