<?php

class m130528_120034_create_contest_table extends CDbMigration {

	public function up() {
	   $this->execute('CREATE TABLE IF NOT EXISTS `contest` (
  			   `contestId` int(11) NOT NULL AUTO_INCREMENT,
  			   `contestTitle` varchar(100) NOT NULL,
  			   `contestSlug` varchar(100) NOT NULL,
  			   `imagePath` varchar(100) NOT NULL,
  			   `startDate` datetime NOT NULL,
  			   `EndDate` datetime NOT NULL,
  			   `creationDate` datetime NOT NULL,
  			   `contestDescription` text NOT NULL,
  			   PRIMARY KEY (`contestId`)
			 ) ENGINE=MyISAM');
	}


	public function down() {
	   $this->execute('DROP TABLE contest');
	}

}
