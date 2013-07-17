<?php

class m130717_063207_category extends CDbMigration {

  public function up() {
    $this->execute("CREATE TABLE IF NOT EXISTS `category` (
      `category_id` int(11) NOT NULL AUTO_INCREMENT,
      `contest_id` varchar(100) NOT NULL,
      `category_name` varchar(100) NOT NULL,
      `creation_date` datetime NOT NULL,
      `status` tinyint(3) NOT NULL,
      PRIMARY KEY (`category_id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 "
    );
  }

  public function down() {
    $this->execute("DROP table `category`");
  }  
}