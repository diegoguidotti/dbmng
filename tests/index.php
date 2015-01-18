<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- Import JQuery and jQuerymobile -->
<script src="../libs/jquery-1.9.1.min.js"></script>

<!-- Import dbmng library -->
<script type="text/javascript" src="../library/dbmng/assets/dbmng_obj.js?mpvqml"></script>
<script type="text/javascript" src="../library/dbmng/assets/dbmng_widgets.js?mpvqml"></script>

<?php

	//import minimum required library for js version
	if(isset($_REQUEST['test_js'])){
		
			echo('<script src="../libs/jstorage.min.js"></script>');

	}
	//import minimum requiredd library for jQueryMobile version
	else if(isset($_REQUEST['test_mobile'])){
			echo('<script src="../libs/jquery.mobile-1.3.2.min.js"></script>');
			echo('<link rel="stylesheet" href="../libs/jquery.mobile-1.3.2.min.css" />');
			echo('<script src="../libs/jstorage.min.js"></script>');
	}

?>


<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>

<body>

<?php

$html='';



/* The test use the test_dbmng database. Create the db using the following script

Create database test_dbmng;

CREATE TABLE `test_dbmng`.`test_base` (
  `id_test` INT  NOT NULL AUTO_INCREMENT,
  `text_field` VARCHAR(255) ,
  `int_field` INT ,
  `file_field` VARCHAR(255) ,
  PRIMARY KEY (`id_test`)
);

*/


//define the db name (the DBMNG_DB_NAME in cfg will be ignored)
define( 'DBMNG_DB_NAME'      , 'test_dbmng' );


//create a standard aForm used by all tools
$aForm=array(  
	'table_name' => 'test_base' ,
		'primary_key'=> array('id_test'), 
		'fields'     => array(
		    'id_test'  => array('label'   => 'ID Record', 'type' => 'int', 'key' => 1 )		,		    
		    'text_field'  => array('label'   => 'Text Field', 'type' => 'varchar','key' => 0 )	,			    
		    'int_field'  => array('label'   => 'INt Field', 'type' => 'int','key' => 0 )				    
		)
);



if(isset($_REQUEST['test_js'])){
	
}
else if (isset($_REQUEST['test_php'])){

	$html.='<h1>Test PHP DBMNG Library</h1>';


	//we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'mysql' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');

	//need to add hidden_var to the php links
  $aParam['hidden_vars']['test_php']='ok';

	$html.= dbmng_crud($aForm, $aParam);

}
else if (isset($_REQUEST['test_mobile'])){

}
else{


	$html.='<h1>Test DBMNG Library</h1>';
	$html.='<ul>';
	$html.='<li><a href="?test_js=ok">Test Javascript</a></li>';
	$html.='<li><a href="?test_php=ok">Test PHP</a></li>';
	$html.='<li><a href="?test_mobile=ok">Test Mobile</a></li>';
	$html.='</ul>';
}

echo($html);

?>
</body>
