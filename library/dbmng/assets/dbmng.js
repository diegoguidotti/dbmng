


/*DBMNG LIBRARY*/
function dbmng_create_table (data, aForm, aParam) {
  //console.log(data);

	var html="<h1 class='dbmng_table_label'>" + t('Table') + ": " + aForm.table_name + "</h1>\n";
	html += "<table>\n";
	
	html += layout_table_head(aForm['fields']);
	
	jQuery.each(data.records, function(index, value) {	
			var o = value;
			html += "<tr>";
			var id_record = 0;

			for( var key in o )
			{        
				//get the field parameters
        var f = aForm.fields[key];

				if( id_record == 0 && key == aForm.primary_key[0] )
					{
						id_record = o[key];
					}

			  if( layout_view_field_table(f.skip_in_tbl) ){
					if (o.hasOwnProperty(key))
					{
						html += "<td>" + o[key] + "</td>";
					}
					else{
						html += "<td>-</td>";
					}
				}
			}

			// available functionalities
			html += "<td class='dbmng_functions'>";
			
			console.log(o);
			console.log(aForm.primary_key[0]);
			console.log(id_record);
			html += layout_table_action( aForm, aParam, id_record );

			html += "</td>\n";
			html += "</tr>\n";
  		//console.log(value);
	});
	html+='</table>\n';


	var nIns=1;

	if (typeof aParam['user_function'] != 'undefined')
		{
			if (typeof aParam['user_function']['ins'] != 'undefined'){
				nIns = aParam['user_function']['ins'];		
			}
		}

  if( nIns == 1) 
		{
		  var base_code = aForm.table_name;
			html += '<a href="#" class="dbmng_insert_button" id="'+base_code+'_ins" >' + t('Insert') + '</a>' + "&nbsp;";

		  jQuery(document).delegate('#'+base_code+'_ins', 'click', function(){ 
	       	dbmng_insert( aForm, aParam);
					return false;			//to avoi to reload the page
		  }); 
		}

	return html;
}





/*LAYOUT LIBRARY*/

function layout_view_field_table(fld_value){
	ret=true;	
	if (typeof fld_value != 'undefined') {
		if(fld_value == 1){
			ret=false;
		}
	}
	return ret;
}

function layout_table_head(aFields){
	html  = "";
	html += "<thead>\n";
	html += "<tr>\n";
	// console.log( aFields );
	jQuery.each(aFields, function(index, field){ 
			var f = field;
			if( layout_view_field_table(f.skip_in_tbl) ){
				html += "<th class='dbmng_field_$fld'>" + t(f.label) + "</th>\n";
			}
	});
	html += "<th class='dbmng_functions'>" + t('actions') + "</th>\n";
	html += "</tr>\n";
	html += "</thead>\n";
	return html;
}

function layout_table_action( aForm, aParam, id_record )
{

  var base_code = aForm.table_name+'_'+id_record;

	var nDel = 1;	
	var nUpd = 1; 	
	var nDup = 1; 
	// get user function parameters
	if (typeof aParam['user_function'] != 'undefined')
	{
		if (typeof aParam['user_function']['upd'] != 'undefined'){
		  nUpd = aParam['user_function']['upd'];		
		}
		if (typeof aParam['user_function']['del'] != 'undefined'){
		  nDel = aParam['user_function']['del'];		
		}
		if (typeof aParam['user_function']['dup'] != 'undefined'){
		  nDup = aParam['user_function']['dup'];		
		}
	}
	
	html = '';

	//probably we do not need this
	//hv   = prepare_hidden_var(aParam);
	if( nDel == 1 )
		{
				html += '<a href="#" class="dbmng_delete_button" id="'+base_code+'_del" >' + t('Del') + '</a>' + "&nbsp;";

			  jQuery(document).delegate('#'+base_code+'_del', 'click', function(){ 
		       	dbmng_delete( aForm, aParam, id_record);
						return false;			//to avoi to reload the page
			  }); 
		}
	if( nUpd == 1 ) 
		{
				html += '<a href="#" class="dbmng_update_button" id="'+base_code+'_upd" >' + t('Upd') + '</a>' + "&nbsp;";

			  jQuery(document).delegate('#'+base_code+'_upd', 'click', function(){ 
		       	dbmng_update( aForm, aParam, id_record);
						return false;			//to avoi to reload the page
			  }); 

		}
	if( nDup == 1 )
		{
			html += '<a href="#" class="dbmng_duplicate_button" id="'+base_code+'_dup" >' + t('Dup') + '</a>' + "&nbsp;";

			  jQuery(document).delegate('#'+base_code+'_dup', 'click', function(){ 
		       	dbmng_update( aForm, aParam, id_record);
						return false;			//to avoi to reload the page
			  }); 
		}
		
	return html;
}

/* probably we do not need this (at least for CRUD operators) we do not reload the page
function prepare_hidden_var(aParam)
{
	hv = "";
	if( typeof aParam['hidden_vars'] != 'undefined' )
		{
//			jQuery.each(aParam['hidden_vars'], function(index, field){ 
//				hv+= ('&amp;' + fld + '=' + fld_value);
//			});
		// console.log( aParam['hidden_vars'] );


		}
	return hv;
}
*/

//call the ajax file to communicate the record deletion
function dbmng_delete( aForm, aParam, id_record){
	ok= confirm(t('Are you sure?'));
	if(ok){
		alert('We need to implement the delete function '+id_record+' table '+aForm.table_name );
		//href="?del_' + aForm['table_name'] + '=' + id_record + hv + '"
	}
}

//call the ajax file to communicate the record update
function dbmng_update(aForm, aParam,id_record){
		alert('We need to implement the UPDATE function');
}


function dbmng_duplicate(aForm, aParam,id_record){
		alert('We need to implement the DUPLICATE function');
}

function dbmng_insert(aForm, aParam){
		alert('We need to implement the INSERT function');
}




/*GENERAL LIBRARY*/
function t (value){
	return Drupal.t(value);
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
