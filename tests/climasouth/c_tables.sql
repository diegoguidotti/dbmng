CREATE TABLE  `enpiclima`.`c_country` (
  `id_c_country` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(255) NOT NULL,
  `flag` varchar(255) NOT NULL,
  `geojson` text,
  PRIMARY KEY (`id_c_country`)
) ENGINE=MyISAM;




CREATE TABLE  `enpiclima`.`c_visibility` (
  `id_c_visibility` int(11) NOT NULL AUTO_INCREMENT,
  `visibility_label` varchar(255) NOT NULL,
  PRIMARY KEY (`id_c_visibility`)
) ENGINE=MyISAM;


CREATE TABLE  `enpiclima`.`c_doc` (
  `id_c_doc` int(11) NOT NULL AUTO_INCREMENT,
  `doc_path` varchar(255) DEFAULT NULL,
  `doc_title` varchar(255) DEFAULT NULL,
  `doc_description` text,
  `id_c_country` int(11) DEFAULT NULL,
  `id_c_visibility` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_c_doc`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1




