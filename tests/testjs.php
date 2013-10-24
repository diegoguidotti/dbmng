<html>
<head>
<script type="text/javascript" src="../library/dbmng/assets/dbmng.js?mpvqml"></script>

<!-- jQuery and JQ Mobile -->
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>


<style type="text/css" media="all">
	@import url("../library/dbmng/assets/dbmng.css");
</style>
</head>
<body>

<table border=1 id='dbmng_table'></table>


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
	
});




</script>

</body>
</html>
