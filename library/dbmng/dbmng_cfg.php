<?php

    if(!defined( 'DBMNG_CMS'))
			{
				define( 'DBMNG_CMS'          , 'drupal' );  // available value: 'drupal', 'none' ('joomla' will follows
			}

    if(!defined( 'DBMNG_DB'))
			{
				define( 'DBMNG_DB'           , 'mysql' );   // available value: 'mysql', 'postgres', 'pdo'
			}

		define( 'DBMNG_CMS'          , 'drupal' ); // available value: 'drupal', 'joomla', 'none'
		define( 'DBMNG_DB'           , 'mysql' ); // available value: 'mysql', 'mysqli', 'postgres', 'pdo'
		define( 'DBMNG_DB_HOST'      , 'localhost' );
		define( 'DBMNG_DB_NAME'      , 'clima' );
		define( 'DBMNG_DB_USER'      , 'admin' );
		define( 'DBMNG_DB_PASS'      , '' );
		define( 'DBMNG_DB_TBL_PREFIX', 'clima' );
?>
