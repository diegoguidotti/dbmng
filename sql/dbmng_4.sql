ALTER TABLE  `dbmng_fields` ADD  `field_widget` VARCHAR( 50 ) NOT NULL AFTER  `id_field_type`;

CREATE TABLE  dbmng_type_table (
id_dbmng_type_table INT NOT NULL AUTO_INCREMENT ,
type_table VARCHAR( 100 ) NOT NULL ,
PRIMARY KEY (  id_dbmng_type_table )
) ENGINE = MYISAM ;

insert into dbmng_type_table values (1, 'Content table');
insert into dbmng_type_table values (2, 'System table');

--view
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dbmng_tables_ext` AS select `t`.`id_table` AS `id_table`,`t`.`id_table_type` AS `id_table_type`,`t`.`table_name` AS `table_name`,`t`.`table_desc` AS `table_desc`,`t`.`table_label` AS `table_label`,count(0) AS `fld` from (`dbmng_tables` `t` left join `dbmng_fields` `f` on((`t`.`id_table` = `f`.`id_table`))) group by `t`.`id_table`,`t`.`id_table_type`,`t`.`table_name`,`t`.`table_desc`,`t`.`table_label`;

