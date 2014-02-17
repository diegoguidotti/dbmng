
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


dbmng_checkbox_prepare_val = function(obj_id, id_record, index){
	var ret= 0;
	if( jQuery('#'+obj_id+'_'+id_record+'_'+index).prop('checked') )
		ret = 1;
	return ret;
}

