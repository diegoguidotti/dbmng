
/*  General class to visualize and edit a single table
*/
function Dbmng( d, f , p) {

  this.aForm = f;

	var newRecords={};
	//we need to create the complex data object;
	jQuery.each(d.records,function(k,v){
		newRecords[Guid.newGuid()]={'state':'ok', 'record':v};
	});
	
	console.log(newRecords);

  this.aData = {'records': newRecords};

  this.aParam = p;

  //debug(data);
  //debug(aForm);
	
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
	
	//show the table and hide the form
	jQuery("#"+this.id+"_view").show();	
	jQuery("#"+this.id+"_form").hide();	

	//store the object to refer to it in the subfunction
	var obj=this;

	//Create the two container for the table and the form
	
	var html='';
	html += "<h1 class='dbmng_table_label'>" + obj.aForm.table_name + "</h1>\n";
	html += "<table class='dbmng_table' id='"+this.id+"_table'>\n";

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

			var o = value.record;
			var state = value.state;
			var id_record=k;

			var html_row = "<tr class='"+state+"'>";
			for( var key in o )
				{        
					//get the field parameters
		      var f = obj.aForm.fields[key];
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

		if(state=='del')
			{
				html_row += '<span id="'+obj.id+'_restore_'+id_record+'"><a  class="dbmng_restore_button"  >' + t('Restore') +'</a>' + "&nbsp;</span>";
			}
		else
			{
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
			}
		
		if(value.error){
			html_row += '<span title="'+value.error+'" class="dbmng_error">'+t('Error!')+'</span>';
		}

		html_row += "</td>\n";
		html_row += "</tr>\n";	

		//Save the table row in DOM
		jQuery('#'+obj.id+'_view tbody').append(html_row);			


		jQuery('#'+obj.id+'_del_'+id_record).click(function(){						
			obj.deleteRecord(id_record);
		});
		jQuery('#'+obj.id+'_restore_'+id_record).click(function(){						
			obj.restoreRecord(id_record);
		});
		jQuery('#'+obj.id+'_upd_'+id_record).click(function(){						
			//obj.updateRecord(id_record);
		});
		jQuery('#'+obj.id+'_dup_'+id_record).click(function(){						
			obj.duplicateRecord(id_record);
		});
	});


	//Add the insert button
	jQuery('#'+obj.id+'_view').append("<a id='"+obj.id+"_add'>"+t("Add")+"</a>");
	jQuery('#'+obj.id+"_add").click(function(){
		obj.createForm();
	});

	jQuery('#'+obj.id+'_view').append(" - <a id='"+obj.id+"_save'>"+t("Save")+"</a>");
	
	jQuery('#'+obj.id+"_save").click(function(){

	var url='dbmng_ajax.php';
	if(obj.aParam.ajax_url){
		url=obj.aParam.ajax_url;
	}
	
		jQuery.ajax({
			url: url,
			type: "POST",
			data: {"aForm" : JSON.stringify(obj.aForm), "inserted":  JSON.stringify(obj.aData.inserted), "deleted": JSON.stringify(obj.aData.deleted) }, 
			dataType: "json",
			success: function (data) {

    		console.log(data);

				if(data.deleted){
					jQuery.each(data.deleted, function(k,v){
						if(v.ok==1){
							delete obj.aData.records[k];
							delete obj.aData.deleted[k];							
						}
						else{
							obj.aData.records[k].error=v.error;
						}
					});
				}


				var pk_key=obj.aForm.primary_key[0];

				if(data.inserted){
					jQuery.each(data.inserted, function(k,v){
						if(v.ok==1){
							obj.aData.records[k].state='ok';
							obj.aData.records[k].record[pk_key] = v.inserted_id;
							delete obj.aData.inserted[k];	
						}
						else{							
							console.log(v);
//							obj.aData.records[k].error=v.error;
						}
					});
				}


				obj.createTable();
				//end of Success	
			},
			error: function (xhr, ajaxOptions, thrownError){
  	  	console.log(xhr);
    	 //console.log(thrownError);
	    }   
		});	
		
	});
	
	html+=get_max_id(obj);
	return html;
};

//The function delete one record
Dbmng.prototype.deleteRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	
	var to_delete = obj.aData.records[id_record];	
	if(to_delete.state=='ins'){
		delete obj.aData.records[id_record];
	}
	else{
		to_delete.state = 'del';
		if(to_delete){
		 	if(!obj.aData.deleted){
					obj.aData.deleted={};
			}
			obj.aData.deleted[id_record]=(to_delete);
		}
		else{
			alert('Error. record to delete not found');
		}		
	}

	obj.createTable();
}				



//The function delete one record
Dbmng.prototype.restoreRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	console.log('restore');
	var to_restore = obj.aData.records[id_record];	
	to_restore.state = 'ok';

	console.log(to_restore);
	delete obj.aData.deleted[id_record];
		
	obj.createTable();
}			

//The function duplicate one record
Dbmng.prototype.duplicateRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	
	var id=[Guid.newGuid()];
	var to_duplicate=jQuery.extend(true, {}, obj.aData.records[id_record]);
	to_duplicate.state = "ins";

	if(to_duplicate)
	{
	 	if(!obj.aData.inserted)
		 	{
				obj.aData.inserted={};
			}
		obj.aData.inserted[id]=to_duplicate;
		obj.aData.records[id]=to_duplicate;
		console.log(id);
		obj.createTable();
	}
}				


//The function insert one record
Dbmng.prototype.insertRecord = function(record) {
	//TODO deal with multiple key
	var obj=this;
		
	var id=[Guid.newGuid()];

	console.log('b');

	if(record){
	 	if(!obj.aData.inserted){
				obj.aData.inserted={};
		}
		obj.aData.inserted[id]=(record);
		obj.aData.records[id]=(record);

		obj.createTable();
	}
	else{
		alert('Error. record to insert undefined');
	}		
}				

// The function get the label from metadb
Dbmng.layout_get_label = function(field_name, field, act)
{
	lb = field.label;
	if( typeof field.label_long != 'undefined' )
		lb = field.label_long;
	
	sRequired = "";
	if(typeof field.nullable != 'undefined' && field.nullable == 0 )
		sRequired = "<span class='dbmng_required'>*</span>";

	/*	
	if( typeof act != 'undefined' )
		{
			if( act != "search" && act != "do_search" )
				{
					if(typeof field.nullable != 'undefined' && field.nullable == 0 )
						sRequired = "<span class='dbmng_required'>*</span>";
				}
		}
		*/
			
		
	return "<label for='"+field_name+"'>" + t(lb) + " " + sRequired + "</label>";
}

// The function add an input widget
Dbmng.layout_form_input = function( fld, field, value, more, act )
{
	html  = "<input type='text' name='"+fld+"' id='"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	
	Dbmng.layout_get_nullable(field,act);
	
	html += " />\n";

	return html;
}

Dbmng.layout_get_nullable = function( field, act )
{
	ht = "";
	if(	typeof field.nullable != 'undefined' && field.nullable == 0 )
			ht += "required ";
			
	/* Check if needed
	if( typeof act != 'undefined' )
		{
			if( act != "search" && act != "do_search" )
				{
					if(	typeof field.nullable != 'undefined' && field.nullable == 0 )
							ht += "required ";
				}
		}
		*/
	return ht;
}

//TODO: review create Form
Dbmng.prototype.createForm = function() {
		var act = 'ins';
		
		//hide the table and show the form
		jQuery("#"+this.id+"_view").hide();	
		jQuery("#"+this.id+"_form").show();	


		obj=this;
		var form='<form >';
		jQuery.each(this.aForm.fields, function(index, field){ 
			
			//console.log(index + ": " + dbmng_check_is_pk(field));
			value = '';
			if( ! dbmng_check_is_pk(field) )
				{
					form += Dbmng.layout_get_label(index, field, act);
					form += Dbmng.layout_form_input(index, field, value, '', act) + "<br/>";
				}

		});
		form+="</form>";
		form+="<a id='"+this.id+"_insert'>"+t("Insert")+"</a>";		

		jQuery('#'+obj.id+"_form").html(form);

		jQuery('#'+obj.id+"_insert").click(function(){
			
			var record= {};
			
			jQuery.each(obj.aForm.fields, function(index, field){ 
				record[index] = jQuery('#'+obj.id+'_form #'+index).val();
			});

			obj.insertRecord({ 'state':'ins', 'record': record});
		});
}



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

function get_max_id(obj)
{
	//var obj = this;
	var id_max = -1;
	var aID = Array();
	
	jQuery.each(obj.aData.records,function(k,value){
		var pk_key=obj.aForm.primary_key[0];
		aID.push(value[pk_key]);
	});	
	aID.reverse();
	
	id_max = "L"+(parseInt(aID[0])+1).toString();
	return id_max;
}

function dbmng_check_is_pk(fld_value)
{
	var ret=false;
	if( typeof fld_value.key == 'undefined' )
		{
			ret = false;
		}
	else if( (parseInt(fld_value.key) == 1 || parseInt(fld_value.key) == 2) )
		{
			ret = true;
		}
	
	return ret;
}


//Creat an object for unique identifier
var Guid = Guid || (function () {

var EMPTY = '00000000-0000-0000-0000-000000000000';

var _padLeft = function (paddingString, width, replacementChar) {
    return paddingString.length >= width ? paddingString : _padLeft(replacementChar + paddingString, width, replacementChar || ' ');
};

var _s4 = function (number) {
    var hexadecimalResult = number.toString(16);
    return _padLeft(hexadecimalResult, 4, '0');
};

var _cryptoGuid = function () {
    var buffer = new window.Uint16Array(8);
    window.crypto.getRandomValues(buffer);
    return [_s4(buffer[0]) + _s4(buffer[1]), _s4(buffer[2]), _s4(buffer[3]), _s4(buffer[4]), _s4(buffer[5]) + _s4(buffer[6]) + _s4(buffer[7])].join('-');
};

var _guid = function () {
    var currentDateMilliseconds = new Date().getTime();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (currentChar) {
        var randomChar = (currentDateMilliseconds + Math.random() * 16) % 16 | 0;
        currentDateMilliseconds = Math.floor(currentDateMilliseconds / 16);
        return (currentChar === 'x' ? randomChar : (randomChar & 0x7 | 0x8)).toString(16);
    });
};

var create = function () {
    var hasCrypto = typeof (window.crypto) != 'undefined',
        hasRandomValues = typeof (window.crypto.getRandomValues) != 'undefined';
    return (hasCrypto && hasRandomValues) ? _cryptoGuid() : _guid();
};

return {
    newGuid: create,
    empty: EMPTY
};})();


