<html>
<head>
<script type="text/javascript" src="../library/dbmng/assets/dbmng.js?mpvqml"></script>
<script type="text/javascript" src="http://localhost/clima/misc/jquery.js?v=1.4.4"></script>
<script type="text/javascript" src="http://localhost/clima/misc/jquery.once.js?v=1.2"></script>

<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>

<table border=1 id='dbmng_table'></table>
Pippo
	<span>G</span> <span>A</span>

<span>lA bella</span>

<script type="text/javascript">
	
	
<?php 

//dbmng_create_json($) 

?>  

var data= {'records':[
		{'id_test':1, 'nome':'Diego', 'eta':39},
		{'id_test':2, 'nome':'Michele', 'eta':41}	
	]};

var form= {'table':'Test', fields: [	]};

jQuery(document).ready(function() {
	
	var html='';
	jQuery.each(data.records, function(index, value) {
		
			html+='<tr><td>'+value.id_test+'</td><td>'+value.nome+'</td><td>'+value.eta+'</td></tr>';
			
			
  		console.log(value);
	});
	
	jQuery('#dbmng_table').html(html);	


$("span:contains('A')").css("color", "red");	

{}

$("span:contains('A')").html('G');	

	
});




</script>

</body>
</html>
