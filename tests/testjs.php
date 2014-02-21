<html>
<head>
<script type="text/javascript" src="../library/dbmng/assets/dbmng_obj.js?mpvqml"></script>
<script type="text/javascript" src="../library/dbmng/assets/dbmng_widgets.js?mpvqml"></script>

<link rel="stylesheet" href="../libs/jquery.mobile-1.3.2.min.css" />
<script src="../libs/jquery-1.9.1.min.js"></script>
<script src="../libs/jquery.mobile-1.3.2.min.js"></script>
<script src="../libs/jstorage.min.js"></script>

<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>

<div id="table2"></div>

<?php 
  //we need to define some global variable to use dbmg independently by Drupal
	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');

	//get the array storing the table metadata from record 1 in table dbmng_tables
	if( gethostname() == "Galveston" || $_SERVER["HTTP_HOST"] == "www.michelemammini.it" )
		$id_table = 1;
	else if( $_SERVER["HTTP_HOST"] == "www.climasouth.eu" )
		$id_table = 20;
	else
		$id_table = 14;

	$aForm    = dbmng_get_form_array($id_table); 


	//print_r($aForm);
	//echo "<br/>" . DBMNG_LIB_PATH;
	//echo dbmng_crud($aForm);
  echo  dbmng_crud_js($aForm, Array() );
?>  

	
<!-- <div id="paste_here" >Paste Here from Excell</div> -->
<script type="text/javascript">
	var db;
  jQuery(document).ready(function() {
		db  = new Dbmng(aForm, {
			'div_element':'table2',   //div id containing the table
			'ajax_url':'ajax.php',    //Where is locate the php with ajax function (relative to the current PHP file)
			'auto_sync': 1,    			  //Save automatically to the server record by record
			'inline':0,               //Enable editing in the table without creating a new form
			'auto_edit':1,            //Run the synch after moving on a new row; auto edit is available only in auto_sync mode
			'mobile':1								//Enable jQuery-mobile css style
		});  
		db.start();
		
		/**
		jQuery('#paste_here').bind('paste', function (e) {
          var tab=(event.clipboardData.getData('text/plain'));
          var lines=(tab.split('\n'));
          var html="<table>";
          var r=0;    
          jQuery.each(lines, function(){
              html+="<tr>";
              var cells=(this.split('\t'));
              var c=0;
              jQuery.each(cells, function(){                                    
                  html+="<td>"+this+"</td>";
                  c++;
              });
              r++;
              html+="</tr>";
          });
          html+="</table>";
          
          jQuery("#paste_here").html(html);
		});
		*/
	});
</script>
</body>
</html>
