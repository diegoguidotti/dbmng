<html>
<head>
<script type="text/javascript" src="../library/dbmng/assets/dbmng_obj.js?mpvqml"></script>

<!-- jQuery and JQ Mobile -->
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>


<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>


<div id="table2"></div>

	
	
<?php 

  //we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/dbmng/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng_standalone.php');

	//get the array storing the table metadata from record 1 in table dbmng_tables
	$aForm    = dbmng_get_form_array(11); 

	//echo dbmng_crud($aForm);
  echo  dbmng_crud_js($aForm, Array() );

?>  


<script type="text/javascript">


  jQuery(document).ready(function() {

	
		var db  = new Dbmng(data, aForm, {'div_element':'table2'});  
		db.createTable();
	
	});




</script>

</body>
</html>
