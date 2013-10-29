

function Dbmng( aData, aForm , aParam) {
  this.aForm = aForm;
  this.aData = aData;
  this.aParam = aParam;

  console.log(data);
  console.log(aForm);
	this.id='dbmng';
	if(this.aParam.div_element){
		this.id='dbmng_'+this.aParam.div_element;
	}
}

Dbmng.prototype.createTable = function()
{

	//store the object to refer to it in the subfunction
	var obj=this;


	var html="";
  html += "<div id='"+this.id+"_form'></div>\n";
  html += "<div id='"+this.id+"_view'>\n";
	html += "<h1 class='dbmng_table_label'>" + t('Table1') + ": " + obj.aForm.table_name + "</h1>\n";
	html += "<table id='"+this.id+"_table'>\n";

	//Add header
	html += "<thead>\n";
	html += "<tr>\n";
	// console.log( aFields );
	jQuery.each(obj.aForm.fields, function(index, field){ 
			var f = field;
			if( layout_view_field_table(f.skip_in_tbl) ){
				html += "<th class='dbmng_field_$fld'>" + t(f.label) + "</th>\n";
			}
	});
	html += "<th class='dbmng_functions'>" + t('actions') + "</th>\n";
	html += "</tr>\n";
	html += "</thead>\n";

  

	jQuery.each(this.aData.records, function(k,value){
			var o = value;
			html += "<tr>";
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
							html += "<td>" + o[key] + "</td>";
						}
						else{
							html += "<td>-</td>";
						}				
					}
			}
		
		// available functionalities
		html += "<td class='dbmng_functions'>";
	
		//TODO: html += layout_table_action( aForm, aParam, id_record );

		html += "</td>\n";
		html += "</tr>\n";
		//console.log(value);
	});
  
  html += "</table>";
	html+="<a id='"+obj.id+"_add'>Aggiungi</a></div>";

	if(this.aParam.div_element){
		jQuery('#'+this.aParam.div_element).html(html);
	}

	jQuery('#'+this.id+"_add").click(function(){
		obj.addRow();
	});


	return html;

};

Dbmng.prototype.createForm = function() {

		var form='This is a form<form>';
		jQuery.each(this.aForm.fields, function(index, field){ 
			form += "<label>" + t(field.label) + "</label><input type='text' value='' />";
		});
		form+="</form>";

		
		jQuery('#'+this.id+"_form").html(form);
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
