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
	
	html = jQuery('<div></div>');

	//probably we do not need this
	//hv   = prepare_hidden_var(aParam);
	if( nDel == 1 )
		{

		function handler() { alert('hello'); }
		html.append(function() {
			return jQuery('<a>Click here</a>').click(handler);
		})

/*

//Primo tentativo
				var removeLink = jQuery("<a id='remove' href='#'>remove</a>").delegate('a','click', function(e) {
						alert('a');
						//Click event handler here.
				});
				html.append(removeLink);  //Add the remove link.
//Secondo tentativo
			var del=jQuery('<a>',{
					text: 'This is blah',
					title: 'Blah',
					//click:function(){alert('test');return false;}
//					click: function(){ dbmng_delete(aForm, aParam, id_record); return false;}
			});
			html.append(del);
			del.live('click', function(){ alert('a') });

//terzo tentativo

			el = jQuery('<a class="" >' + t('Delete') + '</a>');			
			el.click ( function(){
				dbmng_delete(aForm, aParam, id_record);
			});
			html += el[0].outerHTML + "&nbsp;";
*/
		}
/*
	if( nUpd == 1 ) 
		{
			jsc = "dbmng_update("+id_record+")";
			html += '<a onclick="' + jsc + '" >' + t('Update') + '</a>' + "&nbsp;";
		}
	if( nDup == 1 )
		{
			jsc = "dbmng_duplicate("+id_record+")";
			html += '<a onclick="' + jsc + '" >' + t('Duplicate') + '</a>' + "&nbsp;";
		}
*/
		
	return html.html();
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
function dbmng_update(id_record){
		alert('We need to implement the UPDATE function');
}


function dbmng_duplicate(id_record){
		alert('We need to implement the DUPLICATE function');
}

function dbmng_insert(id_record){
		alert('We need to implement the INSERT function');
}




/*GENERAL LIBRARY*/
function t (value){
	return Drupal.t(value);
}

/*DBMNG LIBRARY*/
function dbmng_create_table (data, aForm, aParam) {
  // console.log(data);

	var html="<h1 class='dbmng_table_label'>" + t('Table') + ": " + aForm.table_name + "</h1>\n";
	html += "<table>\n";
	
	html += layout_table_head(aForm['fields']);
	
	jQuery.each(data.records, function(index, value) {	
			var o = value;
			html += "<tr>";
			var id_record = 0;
			for( var key in o )
			{
				if (o.hasOwnProperty(key))
				{
					html += "<td>" + o[key] + "</td>";
					if( id_record == 0 && key == aForm.primary_key[0] )
					{
						id_record = o[key];
					}
				}
			}

			// available functionalities
			html += "<td class='dbmng_functions'>";
			
			console.log(o);
			console.log(aForm.primary_key[0]);
			html += layout_table_action( aForm, aParam, id_record );

			html += "</td>\n";
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
