ALTER TABLE  dbmng_fields ADD  field_label_long VARCHAR( 100 ) NOT NULL AFTER  field_label;
ALTER TABLE  dbmng_fields ADD  skip_in_tbl INT DEFAULT 0;