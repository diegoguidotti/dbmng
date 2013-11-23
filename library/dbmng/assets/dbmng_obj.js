
/*  General class to visualize and edit a single table
*/
function Dbmng( aData, aForm , aParam) {

  this.aForm = aForm;
  this.aData = aData;
  this.aParam = aParam;

  debug(data);
  debug(aForm);

	this.id='dbmng';
	if(this.aParam.div_element){
		this.id='dbmng_'+this.aParam.div_element;
	}

	var html="";
  html += "<div id='"+this.id+"_form'></div>\n";
  html += "<div id='"+this.id+"_view'>\n";
	jQuery('#'+this.aParam.div_element).html(html);

}

Dbmng.prototype.createTable = function()
{
	//store the object to refer to it in the subfunction
	var obj=this;

	//Create the two container for the table and the form
	
	var html='';
	html += "<h1 class='dbmng_table_label'>" + obj.aForm.table_name + "</h1>\n";
	html += "<table id='"+this.id+"_table'>\n";

	//Add header
	html += "<thead>\n";
	html += "<tr>\n";
	jQuery.each(obj.aForm.fields, function(index, field){ 
			var f = field;
			if( layout_view_field_table(f.skip_in_tbl) ){
				html += "<th class='dbmng_field_$fld'>" + t(f.label) + "</th>\n";
			}
	});
	html += "<th class='dbmng_functions'>" + t('actions') + "</th>\n";
	html += "</tr>\n";
	html += "</thead>\n";
	html+= "<tbody></tbody></table>";	

	jQuery('#'+obj.id+'_view').html(html);
  
	//Create the body of the table
	jQuery.each(this.aData.records, function(k,value){

			var o = value;
			var html_row = "<tr>";
			var id_record = 0;
			for( var key in o )
				{        
					//get the field parameters
		      var f = obj.aForm.fields[key];

					if( id_record == 0 && key == obj.aForm.primary_key[0] )
						{
							id_record = o[key];
						}
					if( layout_view_field_table(f.skip_in_tbl) ){
						if (o.hasOwnProperty(key))
						{
							html_row += "<td>" + o[key] + "</td>";
						}
						else{
							html_row += "<td>-</td>";
						}				
					}
			}
		
		// available functionalities
		html_row += "<td class='dbmng_functions'>";
		
		var nDel=1; nUpd=1; nDup=1;

		if(aParam['user_function']){
		  nUpd = ((aParam['user_function']['upd']) ? aParam['user_function']['upd'] : 1);
		  nDel = ((aParam['user_function']['del']) ? aParam['user_function']['del'] : 1);
		  nDup = ((aParam['user_function']['dup']) ? aParam['user_function']['dup'] : 1);
		}

		if( nDel == 1 )
			{				
				html_row += '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button"  >' + t('Delete') +'</a>' + "&nbsp;</span>";
			}
		if( nUpd == 1 )
			{				
				html_row += '<span id="'+obj.id+'_upd_'+id_record+'"><a  class="dbmng_update_button"  >' + t('Update') +'</a>' + "&nbsp;</span>";
			}
		if( nDup == 1 )
			{				
				html_row += '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";
			}

		html_row += "</td>\n";
		html_row += "</tr>\n";	

		//Save the table row in DOM
		jQuery('#'+obj.id+'_view tbody').append(html_row);			


		jQuery('#'+obj.id+'_del_'+id_record).click(function(){						
			obj.deleteRecord(id_record);
		});
		jQuery('#'+obj.id+'_upd_'+id_record).click(function(){						
			//obj.updateRecord(id_record);
		});
		jQuery('#'+obj.id+'_dup_'+id_record).click(function(){						
			//obj.dupplicateRecord(id_record);
		});
	});


	//Add the insert button
	jQuery('#'+obj.id+'_view').append("<a id='"+obj.id+"_add'>"+t("Add")+"</a>");
	jQuery('#'+obj.id+"_add").click(function(){
		obj.addRow();
	});
	

	return html;
};


//The function delete one record
Dbmng.prototype.deleteRecord = function(id_record) {

			//TODO deal with multiple key
			var obj=this;
			to_delete=-1;
			jQuery.each(obj.aData.records,function(k,value){
				var pk_key=obj.aForm.primary_key[0];
				if(value[pk_key]==id_record){
					to_delete=k;
				}
			});

			if(to_delete>-1){
			 	if(!obj.aData.deleted){
						obj.aData.deleted=Array();
				}
				obj.aData.deleted.push(obj.aData.records[to_delete]);

				obj.aData.records.splice(to_delete,1);
				obj.createTable();
			}
			else{
				alert('Error. record to delete not found');
			}		
}				

Dbmng.layout_get_label = function(field_name, field)
{
	lb = field.label;
	if( typeof field.label_long != 'undefined' )
		lb = field.label_long;
	
	sRequired = "";
	
	//if( isset($_REQUEST['act']) )
	//	{
	//		if( $_REQUEST['act'] != "search" && !$_REQUEST['act'] == "do_search" )
	//			{
					if(typeof field.nullable != 'undefined' && field.nullable == 0 )
						sRequired = "<span class='dbmng_required'>*</span>";
	//			}
	//	}
			
		
	return "<label for='"+field_name+"'>" + t(lb) + " " + sRequired + "</label>";
}

Dbmng.layout_form_input = function( fld, field, value, more )
{
	html  = "<input type='text' name='"+fld+"' id='"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	
	if(typeof field.nullable != 'undefined' && field.nullable == 0 )
		html += " required ";	
	
	html += " />\n";

	return html;
}


//TODO: review create Form
Dbmng.prototype.createForm = function() {
		obj=this;
		var form='<form>';
		jQuery.each(this.aForm.fields, function(index, field){ 
			form += Dbmng.layout_get_label(index, field);
			
			console.log(index);
			//keep only input
			if(field.widget=='checkbox'){
				form+="<input type='checkbox' value='' /><br/>";
			}
			else{
				value = '';
				form += Dbmng.layout_form_input(index, field, value, '') + "<br/>";
				//form+="<input id='"+index+"' type='text' value='' /><br/>";
			}

		});
		form+="</form>";
		form+="<a id='"+this.id+"_insert'>"+t("Insert")+"</a>";		

		jQuery('#'+obj.id+"_form").html(form);

		jQuery('#'+obj.id+"_insert").click(function(){
			debug('insert');
		});
}


Dbmng.prototype.addRow = function(){
	jQuery("#"+this.id+"_view").hide();	
	jQuery("#"+this.id+"_form").show();	
	this.createForm();
};

function layout_view_field_table(fld_value){
	ret=true;	
	if (typeof fld_value != 'undefined') {
		if(fld_value == 1){
			ret=false;
		}
	}
	return ret;
}

function t(txt){
	return txt;
}

var DEBUG=true;
function debug(d){
	if(DEBUG){
		console.log(d);
	}
}
