<?php

require_once './sys/inc/include.inc';


$_k['db']->query('DROP TABLE IF EXISTS `'.CFG_DB_PREF.'task`');
$_k['db']->query('CREATE TABLE `'.CFG_DB_PREF.'task`(
`id`    INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
`name`  VARCHAR(20) NOT NULL,
`email` VARCHAR(50) NOT NULL,
`text`  VARCHAR(255),
`done`  CHAR DEFAULT \'n\',
`cdt`   DATETIME,
`cdt_r` CHAR(19),
`udt`   DATETIME,
`udt_r` CHAR(19)
) DEFAULT CHARACTER SET \'utf8\' DEFAULT COLLATE \'utf8_general_ci\'');


echo 'Init successful.';

sys_end();