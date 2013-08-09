CREATE TABLE  c_expert (
  id_c_expert int(11) NOT NULL AUTO_INCREMENT,
  id_c_country int(11) NOT NULL,
  name varchar(255),
  jobtitle varchar(255),
  institution varchar(255),
  phone1 varchar(255),
  phone2 varchar(255),
  address varchar(255),
  email varchar(255),
  skype varchar(255),
  twitter varchar(255),
  PRIMARY KEY (id_c_expert)
) ENGINE=MyISAM;

CREATE TABLE  c_institution (
  id_c_institution int(11) NOT NULL AUTO_INCREMENT,
  id_c_country int(11) NOT NULL,
  name_institution varchar(255),
  phone1 varchar(255),
  phone2 varchar(255),
  address varchar(255),
  email varchar(255),
  PRIMARY KEY (id_c_institution)
) ENGINE=MyISAM;

CREATE TABLE  c_project (
  id_c_project int(11) NOT NULL AUTO_INCREMENT,
  name_project varchar(255),
  PRIMARY KEY (id_c_project)
) ENGINE=MyISAM;
