CREATE TABLE  dbmng_type_table (
id_dbmng_type_table INT NOT NULL AUTO_INCREMENT ,
type_table VARCHAR( 100 ) NOT NULL ,
PRIMARY KEY (  id_dbmng_type_table )
) ENGINE = MYISAM ;

insert into dbmng_type_table values (1, 'Content table');
insert into dbmng_type_table values (2, 'System table');