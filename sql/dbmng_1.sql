ALTER TABLE dbmng_fields ADD pk INT(1) DEFAULT 0;
ALTER TABLE dbmng_fields ADD field_function varchar(100) DEFAULT NULL;

-- ALTER TABLE dbmng_fields ADD field_object varchar( 100 ) DEFAULT 0; -- associated object field
-- ALTER TABLE dbmng_fields ADD field_type   varchar( 1 ) DEFAULT 0; --referred to the object
-- ALTER TABLE dbmng_fields ADD pk INT( 1 ) DEFAULT 0;
