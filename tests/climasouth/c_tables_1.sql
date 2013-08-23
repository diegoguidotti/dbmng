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

ALTER TABLE  dbmng_fields ADD  is_searchable INT( 11 ) NOT NULL AFTER field_order;


/* >>>>>>>>>>>>>>>>>>>>>>>>> 
		NEW TABLE: 13/08/2013 
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
CREATE TABLE  c_news (
  id_c_news int(11) NOT NULL AUTO_INCREMENT,
  news_title varchar(255),
  news_date varchar(255),
  news_content text,
  geom text,
  id_user int(11),
  PRIMARY KEY (id_c_news)
) ENGINE=MyISAM;

ALTER TABLE  c_expert ADD  geom text AFTER twitter;
ALTER TABLE  c_institution ADD  geom text AFTER email;
ALTER TABLE  c_project ADD  geom text;

/* >>>>>>>>>>>>>>>>>>>>>>>>> 
		NEW TABLE: 23/08/2013 
<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<*/
CREATE TABLE  c_ghg_assessment (
  id_c_ghg_assessment int(11) NOT NULL AUTO_INCREMENT,
  id_c_country int(11) NOT NULL,
	id_c_assessment int(11) NOT NULL,
  current_rating int(11) NOT NULL,
  proposed_target int(11) NOT NULL,
  status varchar(255),
  comments text,
  capacity_needs text,
  id_user int(11),
  PRIMARY KEY (id_c_ghg_assessment)
) ENGINE=MyISAM;

CREATE TABLE  c_assessment (
  id_c_assessment int(11) NOT NULL AUTO_INCREMENT,
  assessment varchar(255),
  PRIMARY KEY (id_c_assessment)
) ENGINE=MyISAM;
