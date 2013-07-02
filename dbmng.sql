DROP TABLE IF EXIST dbmng_tables;
DROP TABLE IF EXIST dbmng_fields;

CREATE TABLE dbmng_tables
(
  id_table serial NOT NULL,
  id_table_type integer,
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
  field_name character(50) NOT NULL,
  field_size integer,
  nullable integer,
  field_note text,
  default_value character(100),
  field_label character(100),
  field_order integer,
  CONSTRAINT id_field PRIMARY KEY (id_field),
  CONSTRAINT table_fieldname UNIQUE (id_table, field_name)
);
