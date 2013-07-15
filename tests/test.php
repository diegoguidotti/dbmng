<html>
<head>
<script type="text/javascript" src="../library/dbmng/assets/dbmng.js?mpvqml"></script>
<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>
<?


  //we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/dbmng/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng_standalone.php');


	$rs=dbmng_query("select * from test");
	foreach($rs as $r){
		//print_r($r);
		//echo('_'.$r->id_test.'_<br/>');
	}


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

?>

</body>
</html>
