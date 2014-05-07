


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

function dbmng_multi1(){
  var html='';
  var sOpt="";
  // alert( aMultiSelectData.val1 + " | " + aMultiSelectData.val2 + " | " + aMultiSelectData.val3);
  if( typeof aMultiSelectData.val3 == 'undefined' )
  	{
	    html+="<option value = ' ' selected></option>";
	    jQuery.each(aMultiSelectData.res, function(key, value) {
	      html+="<option value='" + key + "'>"+value.value+"</option>";
	    });
	  }
	 else
	 	{
	    html+="<option value = ' '></option>";
	    jQuery.each(aMultiSelectData.res, function(key, value) {
	    	sOpt = "";
	    	if(key == aMultiSelectData.val1)
	    		sOpt = "selected";
	      html+="<option value='" + key + "' "+sOpt+">"+value.value+"</option>";
	    });
	 	}
  jQuery('#'+aMultiSelectData.field_name+'_res').html(html);
  
  //html = "";
  //html+="<option value = ' ' selected></option>";
  //jQuery('#'+aMultiSelectData.field_name+'_res3').html(html);
}

function dbmng_update_multi2() {
	var html='';
	var sOpt="";

	if( typeof aMultiSelectData.val3 == 'undefined' )
		{
	    var indice=jQuery('#'+aMultiSelectData.field_name+'_res').val();
	
	    html+="<option value = ' ' selected></option>";
	    jQuery.each(aMultiSelectData.res[indice].vals, function(key, value) {
	      html+="<option value='" + key + "'>"+value.value+"</option>";
	    });
	  }
	else
		{
	    html+="<option value = ' '></option>";
	    jQuery.each(aMultiSelectData.res[aMultiSelectData.val1].vals, function(key, value) {
	    	sOpt = "";
	    	if(key == aMultiSelectData.val2)
	    		sOpt = "selected";
	      html+="<option value='" + key + "' "+sOpt+">"+value.value+"</option>";
	    });
		}
  jQuery('#'+aMultiSelectData.field_name+'_res2').html(html);
}

function dbmng_update_multi3() {
	var html='';
	var sOpt="";
	
	if( typeof aMultiSelectData.val3 == 'undefined' )
		{
	    var indice=jQuery('#'+aMultiSelectData.field_name+'_res').val();
	    var indice2=jQuery('#'+aMultiSelectData.field_name+'_res2').val();
	    html+="<option value = ' ' selected></option>";
	    jQuery.each(aMultiSelectData.res[indice].vals[indice2].vals, function(key, value) {
	      html+="<option value='" + key + "'>"+value.value+"</option>";
	    });
	  }
	else
		{
	    html+="<option value = ' '></option>";
	    jQuery.each(aMultiSelectData.res[aMultiSelectData.val1].vals[aMultiSelectData.val2].vals, function(key, value) {
	    	sOpt = "";
	    	if(key == aMultiSelectData.val3)
	    		sOpt = "selected";
	      html+="<option value='" + key + "' "+sOpt+">"+value.value+"</option>";
	    });
		}
	
	jQuery('#'+aMultiSelectData.field_name+'_res3').html(html);
}

function dbmng_update_multi(){
	//alert(jQuery('#'+aMultiSelectData.field_name+'_res3').val());
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

function dbmng_multi (form) {
	
	var sel= jQuery('#'+form+"_from").val();
	
	var html='';
	jQuery.each( jQuery('#'+form+"_from").val(), function (key, val ){
			html+="<option value='"+val+"'>"+val+"</option>";			
	});

	var sel= jQuery('#'+form+"").html(html);
	
	console.log(sel);
	alert('m');
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


//draw a leaflet maps containing data
function dbmng_leaflet(data, aForm, aParam){

	var coord=[40, 13];
	if(aParam.coord)
		coord=aParam.coord;

	var zoom=3;
	if(aParam.zoom)
		zoom=aParam.zoom;


	var base_path=aParam.base_path;

	//create the map objet
  var map = L.map(aParam.div_element).setView(coord, zoom);

	//add a background layer

	if(false){
		var mapquestUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png',
		subDomains = ['otile1','otile2','otile3','otile4'],
		mapquestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors.';
		var mapquest = new L.TileLayer(mapquestUrl, {maxZoom: 18, attribution: mapquestAttrib, subdomains: subDomains});
		map.addLayer(mapquest);  
	}
	else{
		var back_map = L.tileLayer.wms("http://95.240.35.64:8181/geoserver/climasouth/wms", {
		  layers: 'climasouth:countries',
		  format: 'image/png',
		  'BGCOLOR': '0xcfeaf3'					  			
		});

		
		map.addLayer(back_map); 		
	}




  //get all the records adding the countries boundaries
	jQuery.each(data.records, function(k,v){

		

		if(v.geojson!=null){

			//transform the string in an object
			var geo = JSON.parse(v.geojson);
			geo['features'][0]['custom_vars']=v;

			var style={"color": "#ffffff", "opacity": 0, "fillColor": "#ff7800", "fillOpacity": 0, "weight":3};;


			//create a feature
			var feature=L.geoJson(geo, {style: style });

			var html = "<div class='climasouth_popup'>";
			html +='<h3><a href="'+base_path+'climasouth/country?id_c_country='+v.id_c_country+'">'+v.country_name+'</a></h3>';
			html += '<img src="'+base_path+v.flag+'"/><br/>';

			//html += '<a href="'+base_path+'climasouth/country?id_c_country='+v.id_c_country+'">'+Drupal.t('go to page')+'</a></div>';
			feature.bindPopup(html);


			feature.addTo(map);
		}
	});	
}

function dbmng_table_getaction ( idtable, actiontype )
{
  //jQuery('#idtableaction #actiontype').val(actiontype);
  //jQuery('#idtableaction').submit();
  jQuery('#'+idtable +' #actiontype').val(actiontype);
  jQuery('#'+idtable).submit();
}
