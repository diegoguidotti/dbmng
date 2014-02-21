CREATE TABLE  `dbmng_users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique user ID.',
  `name` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Unique user name.',
  `pass` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'User’s password (hashed).',
  `mail` varchar(254) CHARACTER SET utf8 DEFAULT '' COMMENT 'User’s e-mail address.',
  PRIMARY KEY (`uid`)
);



CREATE TABLE `dbmng_users_roles` (
  `uid` INTEGER  NOT NULL,
  `rid` INTEGER  NOT NULL,
  PRIMARY KEY (`uid`, `rid`)
);


CREATE TABLE  `dbmng_role` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique role ID.',
  `name` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'Unique role name.',
  `weight` int(11)  NOT NULL DEFAULT 0 ,  
  PRIMARY KEY (`rid`)
);


insert  into dbmng_users (name, pass, mail) values ('test', md5('test'), 'test@test.it');
insert  into dbmng_role (name) values ('test');
insert  into dbmng_users_roles (uid,rid) values (1,1);



CREATE TABLE  `dbmng_role_tables` (
  `id_table` INTEGER  NOT NULL,
  `rid` INTEGER  NOT NULL,
  `param` text  NOT NULL DEFAULT '' ,  
  PRIMARY KEY (`rid`,`id_table`)
);
