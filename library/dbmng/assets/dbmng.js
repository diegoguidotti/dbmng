
function dbmng_create_table (data, aForm) {
  // console.log(data);

	var html="<h1>Table: " + aForm.table_name + "</h1>\n";
	html += "<table>\n";
	
	// >>>>> start layout_table_head
	html += "<thead>\n";
	html += "<tr>\n";
	
	jQuery.each(aForm['fields'], function(index, field){ 
			var f = field;
			console.log(f.label);    
			//if( layout_view_field_table($fld_value) )
				html += "<th class='dbmng_field_$fld'>" + f.label + "</th>\n";
	});
	html += "<th class='dbmng_functions'>" + ('actions') + "</th>\n";
	html += "</tr>\n";
	html += "</thead>\n";
	// <<<<<<< finish layout_table_head
	
	jQuery.each(data.records, function(index, value) {	
			var o = value;
			html += "<tr>";
			for( var key in o )
			{
				if (o.hasOwnProperty(key))
				{
					html += "<td>" + o[key] + "</td>";
				}
			}
			html += "<td> fnc </td>";
			html += "</tr>\n";
  		//console.log(value);
	});
	html+='</table>\n';
	return html;
}

function dbmng_validate_numeric (evt) {
  var theEvent = evt || window.event;
  var key = theEvent.keyCode || theEvent.which;
  key = String.fromCharCode( key );
  var regex = /[0-9]|\./;
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}


function dbmng_style_fileform(file){
    jQuery("#"+file+"_tmp_choose").click(function(){
        jQuery("#"+file).click();
        return false;
    });
    
    jQuery("#"+file).change(function(){

					var filename=jQuery(this).val();
					//replace facepath used by 
					filename=filename.replace("C:\\fakepath\\",'');
          jQuery("#"+file+"_tmp_choosebox").val(filename);
    });
        
    
    jQuery("#"+file+"_tmp_remove").click(function(){
        jQuery("#"+file+"_tmp_choosebox").val("");
				jQuery("#"+file+"_link_container").hide();

    });

}

function dbmng_tablesorter(id_tbl, nCol){
	var exclude_sorter;
	nCol = nCol;
	exclude_sorter = "{headers: {"+nCol+": {sorter: false}}}";
	exclude_sorter = eval('(' + exclude_sorter + ')'); 
	jQuery(document).ready(function() 
    { 
        jQuery("#"+id_tbl).tablesorter(exclude_sorter); 
    } 
	);
}
