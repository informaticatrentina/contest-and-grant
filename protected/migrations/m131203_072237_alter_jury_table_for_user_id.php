<?php

class m131203_072237_alter_jury_table_for_user_id extends CDbMigration {

  public function up() {
    $this->execute('ALTER TABLE  `jury` ADD  `user_id` VARCHAR( 100 ) NULL AFTER  `email_id`');
  }

  public function down() {
    $this->execute('ALTER TABLE `jury` DROP `user_id`');
  }
}