<?php

	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'mysql' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');


	echo dbmng_ajax_manager();

?>
