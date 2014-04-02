
CREATE TABLE dbmng_tables
(
  id_table INT NOT NULL AUTO_INCREMENT ,
  id_table_type INT( 11 ) NULL DEFAULT  1,
  table_name VARCHAR(250),
  table_desc text,
  table_label text,
  CONSTRAINT id_table PRIMARY KEY (id_table),
  CONSTRAINT table_name UNIQUE (table_name)
);

CREATE TABLE dbmng_fields
(
  id_field INT NOT NULL AUTO_INCREMENT ,
  id_table integer,
  id_field_type integer NOT NULL,
  field_widget VARCHAR(255) NOT NULL,
  field_name VARCHAR(255) NOT NULL,
  field_size integer,
  nullable integer,
  field_note text,
  default_value VARCHAR(255),
  field_label VARCHAR(255),
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


