
CREATE TABLE dbmng_tables
(
  id_table serial NOT NULL,
  id_table_type INT( 11 ) NULL DEFAULT  1,
  table_name character(50),
  table_desc text,
  table_label text,
  CONSTRAINT id_table PRIMARY KEY (id_table),
  CONSTRAINT table_name UNIQUE (table_name)
);

CREATE TABLE dbmng_fields
(
  id_field serial NOT NULL,
  id_table integer,
  id_field_type integer NOT NULL,
  field_widget character(100) NOT NULL,
  field_name character(50) NOT NULL,
  field_size integer,
  nullable integer,
  field_note text,
  default_value character(100),
  field_label character(100),
	field_label_long VARCHAR( 100 ) NOT NULL,
  field_order integer,
	pk INT(1) DEFAULT 0,
	field_function varchar(100) DEFAULT NULL,
	skip_in_tbl INT DEFAULT 0,
	voc_sql VARCHAR ( 255 ),
  CONSTRAINT id_field PRIMARY KEY (id_field),
  CONSTRAINT table_fieldname UNIQUE (id_table, field_name)
);


CREATE TABLE  dbmng_type_table (
	id_dbmng_type_table INT NOT NULL AUTO_INCREMENT ,
	type_table VARCHAR( 100 ) NOT NULL ,
	PRIMARY KEY (  id_dbmng_type_table )
);

insert into dbmng_type_table values (1, 'Content table');
insert into dbmng_type_table values (2, 'System table');


--view 
CREATE  VIEW `dbmng_tables_ext` AS select `t`.`id_table` AS `id_table`,`t`.`id_table_type` AS `id_table_type`,`t`.`table_name` AS `table_name`,`t`.`table_desc` AS `table_desc`,`t`.`table_label` AS `table_label`,count(0) AS `fld` from (`dbmng_tables` `t` left join `dbmng_fields` `f` on((`t`.`id_table` = `f`.`id_table`))) group by `t`.`id_table`,`t`.`id_table_type`,`t`.`table_name`,`t`.`table_desc`,`t`.`table_label`;


-- insert into dbmng_tables
insert into dbmng_tables (id_table, id_table_type, table_name, table_label) values (8, 2, 'dbmng_tables', 'Tables name');
insert into dbmng_tables (id_table, id_table_type, table_name, table_label) values (9, 2, 'dbmng_fields', 'Fields name');

-- insert into dbmng_fields                                                                                                                                                  type      name           siz nu  def    label          label long        or  pk  skip
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (8, 'bigint', 'id_table'     , 20, 0, null, 'ID'         , 'ID'               , 1, 1, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (8, 'int'   , 'id_table_type', 11, 0, null, 'Table type' , 'Table type'       , 2, 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (8, 'char'  , 'table_name'   , 50, 0, null, 'Table name' , 'Table name'       , 3, 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (8, 'text'  , 'table_desc'   , 11, 0, null, 'Table desc' , 'Table description', 4, 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (8, 'text'  , 'table_label'  , 11, 0, null, 'Table label', 'Table label'      , 5, 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'bigint' , 'id_field'        , 20 , 0, null, 'ID'         , 'ID'               , 1 , 1, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'id_table'        , 11 , 1, null, 'ID Tbl'     , 'ID Table'         , 2 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'varchar', 'id_field_type'   , 11 , 0, null, 'Type'       , 'Field Type'       , 3 , 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'char'   , 'field_name'      , 50 , 0, null, 'Name'       , 'Field Name'       , 4 , 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'field_size'      , 11 , 1, null, 'Size'       , 'Field Size'       , 5 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'nulable'         , 20 , 1, null, 'Null'       , 'Nullable'         , 6 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'text'   , 'field_note'      , 20 , 1, null, 'Note'       , 'Field Note'       , 7 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'char'   , 'default_value'   , 100, 1, null, 'Default'    , 'Default value'    , 8 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'varchar', 'field_label'     , 100, 0, null, 'Label'      , 'Field Label'      , 9 , 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'varchar', 'field_label_long', 100, 0, null, 'Long'       , 'Long Label'       , 10, 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'field_order'     , 11 , 1, null, 'Order'      , 'Field Order'      , 11, 0, 0);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'pk'              , 1  , 1, 0   , 'pk'         , 'Primary key'      , 12, 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'varchar', 'field_function'  , 100, 1, null, 'Fnc'        , 'Field function'    , 13, 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'int'    , 'skip_in_tbl'     , 11 , 1, 0   , 'Skip'       , 'Skip in table view', 14, 0, 1);
insert into dbmng_fields (id_table, id_field_type, field_name, field_size, nullable, default_value, field_label, field_label_long, field_order, pk, skip_in_tbl) values (9, 'varchar', 'voc_sql'         , 255, 1, null, 'sql'        , 'Voc sql'           , 15, 0, 1);

