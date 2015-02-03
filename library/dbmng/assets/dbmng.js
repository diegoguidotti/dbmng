


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

	
	var mapquestUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png',
	subDomains = ['otile1','otile2','otile3','otile4'],
	mapquestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors.';
	var mapquest = new L.TileLayer(mapquestUrl, {maxZoom: 18, attribution: mapquestAttrib, subdomains: subDomains});
	map.addLayer(mapquest);  



  //get all the records adding the countries boundaries
	jQuery.each(data.records, function(k,v){

		

		if(v.geojson!=null){

			//transform the string in an object
			var geo = JSON.parse(v.geojson);
			geo['features'][0]['custom_vars']=v;

			var style={"color": "#ffffff", "opacity": 0.7, "fillColor": "#ff7800", "fillOpacity": 0.7, "weight":3};;


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


function dbmng_reduce_fields ( field_selector, lun )
{
	console.log('start');
	jQuery(function(){
		jQuery(field_selector).each(function(k,v){
			var v=jQuery(v);
			var content=v.html();
			console.log(content.length);
			
			if(content.length>lun){
				var html='<span class="reduced">'+content.substring(0,lun)+'...</span><span style="display:none" class="complete">'+content+'</span>';	
				v.html(html);

				v.click(function(){
						v.children('span').toggle();				
				});
			}

			
		});
	});
}

function dbmng_nmimage(key, val, fld, path){
	var html="";
  var i=jQuery("#dbmng_nmimage_"+fld+"_div");
	
	html += "<div class='ui-widget ui-helper-clearfix'>";
	html += "<ul id='gallery' class='gallery ui-helper-reset ui-helper-clearfix'>";

	jQuery.each(key, function(k,v) {
			html += "  <li class='ui-widget-content ui-corner-tr'>";
			html += "    <h5 class='ui-widget-header'>"+v['title']+"</h5>";
			html += "    <input type='hidden' id='dbmng_id_selected' value='"+k+"' />";
			html += "    <img src='"+path+v['image']+"' width='96' height='72'>";
			html += "    <a href='"+path+v['image']+"' title='View larger image' class='ui-icon ui-icon-zoomin'>View larger</a>";
			html += "  </li>";
	});
	
	html += "</ul>";
	html += "<div id='dbmng_selected_img' class='ui-widget-content ui-state-default'>";
	html += "  <h4 class='ui-widget-header'>Selected</h4>";
	html += "<select id='dbmng_selected' >";//style='visibility: hidden'
	html += "</div>";
	html += "</div>";	
	
	i.append(html);
	
	// there's the gallery and the trash
	var gallery = jQuery( "#gallery" ),
		dbmng_selected_img = jQuery( "#dbmng_selected_img" );

	// let the gallery items be draggable
	jQuery( "li", gallery ).draggable({
		cancel: "a.ui-icon", // clicking an icon won't initiate dragging
		revert: "invalid", // when not dropped, the item will revert back to its initial position
		containment: "document",
		helper: "clone",
		cursor: "move"
	});

	// let the trash be droppable, accepting the gallery items
	dbmng_selected_img.droppable({
		accept: "#gallery > li",
		activeClass: "ui-state-highlight",
		drop: function( event, ui ) {
			var item = ui.draggable
			selectImage( item );
		}
	});

	// let the gallery be droppable as well, accepting items from the trash
	gallery.droppable({
		accept: "#dbmng_selected_img li",
		activeClass: "custom-state-active",
		drop: function( event, ui ) {
			unselectImage( ui.draggable );
		
			jQuery("#dbmng_selected").html("");
			
		}
	});
	/*	var i=jQuery("#dbmng_nmimage_"+fld+"_div");

	jQuery.each(key, function(k,v) {
		var html = "<div draggable='true' class='dbmng_image' id='dbmng_"+fld+"_"+k+"'>"
		html+='<img src="'+path+v['image']+'"></img><br/>'+v['title'];
		html += "</div>";
		i.append(html);
		
		jQuery('#dbmng_'+fld+'_'+k).ondragstart= function(evt){			
			console.log('drag start');
		};
		
		console.log(jQuery('#dbmng_'+fld+'_'+k));
		
		jQuery('#dbmng_'+fld+'_'+k).ondrop= function(evt){			
			console.log('drop');
		};
		//html+='<img src="'+path+v['image']+'"></img><br/>'+v['title'];
	});
	*///alert(fld);	
}

var pippo;
function selectImage( item ) {
	item.fadeOut(function() {
		var list = jQuery( "ul", dbmng_selected_img ).length ?
			jQuery( "ul", dbmng_selected_img ) :
			jQuery( "<ul class='gallery ui-helper-reset'/>" ).appendTo( dbmng_selected_img );

		item.appendTo( list ).fadeIn(function() {
			item
				.animate({ width: "48px" })
				.find( "img" )
					.animate({ height: "36px" });
		});
		
		//jQuery("#dbmng_selected").html("");
		jQuery("#dbmng_selected").empty();
		jQuery( "ul li", dbmng_selected_img ).each(function(k,v){
			var title  = jQuery(v).children('h5').text();
			var id_img = jQuery(v).children('input').val();
			var o = new Option(title, id_img);
			jQuery(o).html(title);
			jQuery("#dbmng_selected").append(o);
			
		});
// 		var title = jQuery( "ul h5", dbmng_selected_img ).text();
// 		var id_img = jQuery( "ul input", dbmng_selected_img ).val();
// 		var o = new Option(title, id_img);
// 		jQuery(o).html(title);
// 		jQuery("#dbmng_selected").append(o);
	});
}

// image recycle function
function unselectImage( item ) {
	item.fadeOut(function() {
		item
			.css( "width", "96px")
			.find( "img" )
				.css( "height", "72px" )
			.end()
			.appendTo( gallery )
			.fadeIn();
	});
}

// image preview function, demonstrating the ui.dialog used as a modal window
function viewLargerImage( link ) {
	var src = link.attr( "href" ),
		title = link.siblings( "img" ).attr( "alt" ),
		modal = jQuery( "img[src$='" + src + "']" );

	if ( modal.length ) {
		modal.dialog( "open" );
	} else {
		var img = jQuery( "<img alt='" + title + "' width='384' height='288' style='display: none; padding: 8px;' />" )
			.attr( "src", src ).appendTo( "body" );
		setTimeout(function() {
			img.dialog({
				title: title,
				width: 400,
				modal: true
			});
		}, 1 );
	}
}
