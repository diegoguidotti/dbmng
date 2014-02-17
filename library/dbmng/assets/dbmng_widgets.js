
//For each widget we need three custom function

function executeFunctionByName(functionName, context /*, args */) {
  var args = Array.prototype.slice.call(arguments).splice(2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
  for(var i = 0; i < namespaces.length; i++) {
    context = context[namespaces[i]];
  }
  return context[func].apply(this, args);
}


/*
* dbmng_*_prepare_val take the value from the form and prepare to be insert in the record
*/

dbmng_widget_prepare_val = function(obj_id, id_record, index){
	return jQuery('#'+obj_id+'_'+id_record+'_'+index).val();
}

/*
* dbmng_*_form generate the html form for the field
*/
dbmng_widget_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html  = "<input type='text' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);
	html += " />\n";
	return html;
}


/*
* dbmng_*_html transform the record value in html fields
*/
dbmng_widget_html = function(val, field ){
	var ret  = val;
	return ret;
}


/*
dbmng_xxxx_prepare_val = function(obj_id, id_record, index){
	return val;
}

dbmng_xxxx_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	return html;
}

dbmng_xxxx_html = function(val, field ){
	var ret  = val;
	return ret;
}

*/




dbmng_checkbox_prepare_val = function(obj_id, id_record, index){
	var ret= 0;
	if( jQuery('#'+obj_id+'_'+id_record+'_'+index).prop('checked') )
		ret = 1;
	return ret;
}

dbmng_checkbox_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	html = "<input class='dbmng_checkbox' type='checkbox' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
  if( value == 1 || (value != 0 && field.default == 1) )
    {
			html += " checked='true' ";
		}	
  //the field will never reply with a null value (true or false)
	//if setted as a non_nullable it will accept only true values
	//$html .= layout_get_nullable($fld_value);	
	html += " />\n";
	return html;
}

dbmng_checkbox_html = function(val, field ){
	if(val==0)
		ret='no';
	else
		ret='si';
	return ret;
}

dbmng_textarea_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	html  = "<textarea  name='" + fld + "' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
				html += Dbmng.layout_get_nullable(field,act);
				html += " >\n";
				html += value;	
				html += "</textarea>\n";
	return html;
}


/* review date form
dbmng_date_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	if( typeof value!="undefined"  && value!='' )
		{
			datetime = new Date(value);             //DateTime::createFromFormat('Y-m-d', $value);
			datetime_str= datetime.toString("d-m-Y"); //datetime->format('d-m-Y');
		}
			
	//add a new input field for the datapicker ui
	html  = "<input type='text' name='"+fld+"_tmp' id='"+obj_id+"_"+id_record+"_"+fld+"_tmp' value='"+datetime_str+"' />";
	//keep hidden the "real" input form
	html += "<input type='hidden' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);	
	html += " />\n";

	return html;
}
*/




