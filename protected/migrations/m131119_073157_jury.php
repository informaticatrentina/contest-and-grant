<?php

class m131119_073157_jury extends CDbMigration {

  public function up() {
    $this->execute('CREATE TABLE IF NOT EXISTS `jury` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `contest_id` int(11) NOT NULL,
      `email_id` varchar(100) NOT NULL,
      `designation` varchar(100) NOT NULL,
      `creation_date` int(10) NOT NULL,
      PRIMARY KEY (`id`)
    )' );
  }

  public function down() {
    $this->execute('DROP TABLE jury');
  }

}