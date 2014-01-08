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
	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');

	//get the array storing the table metadata from record 1 in table dbmng_tables
	if( gethostname() == "Galveston" )
		$id_table = 12;
	else
		$id_table = 11;
	
	$aForm    = dbmng_get_form_array($id_table); 
	//echo dbmng_crud($aForm);
  echo  dbmng_crud_js($aForm, Array() );

?>  

	
<div id="paste_here" >Paste Here from Excell</div>

<script type="text/javascript">
	var db;


  jQuery(document).ready(function() {

	
		db  = new Dbmng(data, aForm, {
			'div_element':'table2',   //div id containing the table
			'ajax_url':'ajax.php',    //Where is locate the php with ajax function (relative to the current PHP file)
			'inline':1                //Enable editing in the table without creating a new form
		});
  
		db.createTable();


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

	
	});




</script>

</body>
</html>
