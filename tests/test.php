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
<?php


  //we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'mysql' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');

	//get the array storing the table metadata from record 1 in table dbmng_tables
	if( gethostname() == "galveston" )
		{
			$aForm    = dbmng_get_form_array(4); 
			/**
			$aForm['fields']['id_mm_agenda_mm_contact'] = Array(
				'label'   => 'Contatti', 
				'key' => 0, 
				'type' => 'integer', 
				'widget'=>'select_nm', 
				'voc_val' => Array('1'=>'Michele', '2'=>'Diego') ,
				'table_nm'=>'mm_agenda_mm_contact', 
				'field_nm'=>'id_mm_contact'
			);
			*/
		}
	else
		{
			$aForm    = dbmng_get_form_array(11); 
			
			//in param: {'table_nm':'diego_c_country', 'field_nm':'id_c_country'}
			/*
			$aForm['fields']['aaa_id_diego_c_country'] = Array(
				'label'   => 'Countries', 
				'key' => 0, 
				'type' => 'integer', 
				'widget'=>'select_nm', 
				'voc_val' => Array('1'=>'Italia', '2'=>'Francia') ,
				'table_nm'=>'diego_c_country', 
				'field_nm'=>'id_c_country'
			);

			echo '<pre>';
			print_r ($aForm);
			echo '</pre>';
 		  */

			
		}

/*
	$aQueries=Array();

	//$aQueries[0]=Array();
	$aQueries[0]['sql']="insert into diego (pippo, data, id_voc_type) values ('bbb', :data, :id_voc_type)";
	$aQueries[0]['var']=Array(':data'=>'2001-01-01', ':id_voc_type'=>2 );
		
	$aQueries[1]['sql']="insert into diego (pippo, data) values ('bbb', :data)";
	$aQueries[1]['var']=Array(':data'=>'2001-01-01' );
	

	print_r(dbmng_transactions($aQueries));
*/



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
