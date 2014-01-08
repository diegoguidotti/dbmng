<html>
<head>

<!-- Load jQuery Library -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />

<!-- Load DBMG JS&CSS -->
<script type="text/javascript" src="../library/dbmng/assets/dbmng.js?mpvqml"></script>
<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>
<?


  //we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');

	//get the array storing the table metadata from record 1 in table dbmng_tables
	$aForm    = dbmng_get_form_array(11); 

	echo dbmng_crud($aForm);

/*
	$aForm=array(  
		'table_name' => 'test' ,
		  'primary_key'=> array('id_test'), 
		  'fields'     => array(
		      'nome' => array('label'   => 'Name', 'type' => 'varchar') ,
		      'eta'  => array('label'   => 'Age' , 'type' => 'int'    )
		  )
	);
	$aParam=array();
	echo dbmng_crud($aForm, $aParam);
*/

?>

</body>
</html>
