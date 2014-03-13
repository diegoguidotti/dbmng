
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

dbmng_select_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	html = "<select  name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
	html += Dbmng.layout_get_nullable(field,act);
	html += " >\n";
	html += "<option/> \n";	

	jQuery.each(field.voc_val, function(k, v){
		s = "";
		debug(k+" "+value+ " " + (k==value));
		if(k == value){
			s = " selected='true' ";
		}
		html += "<option "+s+" value='" + k + "'>" + v + "</option> \n";	
	});
	html += "</select>\n";
	return html;
}

dbmng_select_html = function(val, field ){
	var ret="-";
	if(field.voc_val[val])
		ret  = field.voc_val[val];
	return ret;
}

dbmng_checkbox_prepare_val = function(obj_id, id_record, index){
	var ret= 0;
	if( jQuery('#'+obj_id+'_'+id_record+'_'+index).prop('checked') )
		ret = 1;
	return ret;
}

dbmng_checkbox_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html='';
	html = "<input class='dbmng_checkbox' type='checkbox' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
  if( value == 1 || (value != 0 && field.default == 1) ){
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


dbmng_date_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html  = "<input type='date' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);
	html += " />\n";
	return html;
}

dbmng_date_html = function(val, field ){
	if(val){
		if(val!='0000-00-00'){
			datetime = new Date(val);
			var mm=datetime.getMonth()+1;
			if(mm<10)
				mm="0"+mm;

			var dd=datetime.getDate();
			if(dd<10)
				dd="0"+dd;

			return dd+"-"+mm+"-"+datetime.getFullYear();
		}
		else{
			return "-";
		}
	}
	else{
		return "-";
	}
}

dbmng_password_form = function(obj_id,  fld, field, id_record, value, more, act ){
	var html  = "<input type='password' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);
	html += " />\n";
	return html;
}
//<button onclick="getImage();">Upload a Photo</button>
dbmng_picture_form = function(obj_id,  fld, field, id_record, value, more, act ){
	html = '<button onclick="dbmng_getImage();">Upload a Photo</button>';
	html += "<input type='input' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);
	html += " />\n";
	return html;
}

dbmng_getImage = function(){
  // Retrieve image file location from specified source
  if(is_cordova()){
	  navigator.camera.getPicture(uploadPhoto, function(message) {
			alert('get picture failed');
			},{
			quality: 50, 
			destinationType: navigator.camera.DestinationType.FILE_URI,
			sourceType: navigator.camera.PictureSourceType.PHOTOLIBRARY
		});
	}
	else{
		debug('Image upload available only in mobile');	
	}
}
 
function uploadPhoto(imageURI) {
	debug("imageURI: "+imageURI);
}

dbmng_password_html = function(val, field ){
	var ret  = '';

	if( val != null )
		ret = '*****';
		
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
