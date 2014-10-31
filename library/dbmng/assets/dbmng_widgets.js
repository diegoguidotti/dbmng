
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

	console.log(field);
	var ty="text";
	if(field.type=='double')
		ty="number"
	var html  = "<input type='"+ty+"' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
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
	if(field.voc_val){
		jQuery.each(field.voc_val, function(k, v){
			s = "";
			debug(k+" "+value+ " " + (k==value));
			if(k == value){
				s = " selected='true' ";
			}
			html += "<option "+s+" value='" + k + "'>" + v + "</option> \n";	
		});
	}
	html += "</select>\n";
	return html;
}

dbmng_select_html = function(val, field ){
	var ret="-";
	if(field.voc_val){
		if(field.voc_val[val])
			ret  = field.voc_val[val];
	}
	return ret;
}

dbmng_checkbox_prepare_val = function(obj_id, id_record, index){
	var ret= 0;

	console.log(('#'+obj_id+'_'+id_record+'_'+index));
	
	if( jQuery('#'+obj_id+'_'+id_record+'_'+index).prop('checked') )
		ret = 1;
	return ret;
}

dbmng_checkbox_form = function(obj_id,  fld, field, id_record, value, more, act ){



/*
	var html='';//dbmng_checkbox 
	html = "<input class='custom' type='checkbox' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' ";
  if( value == 1 || (value != 0 && field.default == 1) ){
		html += " checked='true' ";
	}	


  //the field will never reply with a null value (true or false)
	//if setted as a non_nullable it will accept only true values
	//$html .= layout_get_nullable($fld_value);	
	html += " / >\n";
*/
	//html+='<input id=new type=checkbox name=news><label for=news>'+field.label+'</label>';
	
	   html='<input class="custom"  name="'+fld+'" id="'+obj_id+"_"+id_record+"_"+fld+'" type="checkbox"  ';
		if( value == 1 || (value != 0 && field.default == 1) ){
			html += ' checked="true" ';
		}	

		 html+=' />';
		 html+='<label class="dbmng_checkbox_label" for='+obj_id+"_"+id_record+"_"+fld+'>'+field.label+'</label>';
	return html;
}

dbmng_checkbox_html = function(val, field ){
	if(val==0)
		ret='<input type="checkbox" disabled="true" />  ';
	else
		ret='<input type="checkbox" checked="false" disabled="true"  />  ';
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


dbmng_gps_form = function(obj_id,  fld, field, id_record, value, more, act ){
	
	var html  = "<input type='hidden' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
	html += " value= '"+value+"' ";	
	html += Dbmng.layout_get_nullable(field,act);
	html += " />\n";

	value=value.trim();
	var cc=value.substring(6,value.length-1);

	cc=cc.trim();
	var ll=cc.split(" ");
	

	if(is_cordova()){
		html += "<div class='gps_label' id='"+obj_id+"_"+id_record+"_"+fld+"_label' ";
		html += "><a href='geo:"+ll[1]+","+ll[0]+"'>"+ll[1]+","+ll[0]+"</div>";	
	}
	else{
		html += "<div class='gps_label' id='"+obj_id+"_"+id_record+"_"+fld+"_label' ";
		html += ">value</div>";	
	}

	//html += "<button onclick=\"dbmng_getPosition('"+obj_id+"_"+id_record+"_"+fld+"');\">Get Position</button>";

	html += "<a data-role=\"button\" onclick=\"dbmng_getPosition('"+obj_id+"_"+id_record+"_"+fld+"');\">Get Position</a>";

	if(typeof dbmng_activate_map != 'undefined'){
		html += "<a data-role=\"button\" onclick=\"dbmng_ShowMap('"+obj_id+"_"+id_record+"_"+fld+"',"+ll[1]+","+ll[0]+");\">Show Map</a>";		
	}


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
	var html = '';
	console.log(field);

	if(is_cordova()){

		jQuery.jStorage.set('tmp_picture',{'obj_id':obj_id, 'id_record': id_record, 'fld': fld});

		var camera_only=false;
		if(field.camera_only){
			camera_only=field.camera_only;
		}

		html = '<div data-role="controlgroup" data-type="horizontal">';			
		  html += '<button onclick="dbmng_getImage('+navigator.camera.PictureSourceType.CAMERA+');">Take a Photo</button>';
			if(!camera_only)
			  html += '<button onclick="dbmng_getImage('+navigator.camera.PictureSourceType.SAVEDPHOTOALBUM+');">Upload a Photo</button>';
		html+='</div>';
		html += "<input type='hidden' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
		html += " value= '"+value+"' ";	
		html += Dbmng.layout_get_nullable(field,act);
		html += " />\n";
	
		//the value is a json object with imageURI and a field to chek if it has been uploaded
		var img_src='';
		if(value){
			try{
				var img = JSON.parse(value);
				img_src=img.imageURI;
			}
			catch(e){
				debug ('Error in parsing '+value);
			}
		}

		var exclude=false;

		if(device.version.startsWith("4.4") && device.platform=='Android' ){
			if(!img_src.startsWith('file')){
				exclude=true;
			}
		}

		if(!exclude){
			html+='<img id="'+obj_id+'_'+id_record+'_'+fld+'_image" width="300px" src="'+img_src+'" />';
		}
		else{
			html+="Image loaded";
		}
	}
	else{
		html ='';
		html += "<input type='hidden' name='"+fld+"' id='"+obj_id+"_"+id_record+"_"+fld+"' " + more;
		html += " value= '' ";	
		html += " /><br/>Image upload available only in web version and mobile app.\n";
	}

	return html;
}

dbmng_picture_html = function(val, field ){
	var img_src='';
	if(val){
		try{
			var img = JSON.parse(val);
			img_src=img.imageURI;

			console.log(img_src);
		}
		catch(e){
			debug ('Error in parsing '+val);
		}
	}
	if(is_cordova()){
		var exclude=false;

		if(device.version.startsWith("4.4") && device.platform=='Android' ){
			if(!img_src.startsWith('file') && !img_src.startsWith('http') ){
				exclude=true;
			}
		}
		if(img_src != "" )
			if(!exclude){
				html = "<img src='"+img_src+"' class='dbmng_picture'  />";

			}
			else{
				html= "i";
			}
		else
			html = "";
	}
	else{
		html="";
	}
		
	return html;
}

dbmng_getImage = function(source_type){
  // Retrieve image file location from specified source
  if(is_cordova()){

		
	  navigator.camera.getPicture(uploadPhoto, function(message) {
			alert('get picture failed');
			},{
			quality: 50, 
			destinationType: navigator.camera.DestinationType.FILE_URI,
			sourceType: source_type
		});
	}
	else{
		alert('Image upload available only in mobile');	
	}
}


function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        x.innerHTML = "Geolocation is not supported by this browser.";
    }
}
function showPosition(position) {
    
}
dbmng_ShowMap  = function(id_element, lat, lon){
	
	if(typeof  L != 'undefined' && typeof  dbmng_activate_map != 'undefined'){




		jQuery.mobile.changePage("#"+dbmng_activate_map.map_page);
	
 		var map = new L.Map(dbmng_activate_map.map_container, {        
        center: new L.LatLng(dbmng_activate_map.start_lat,dbmng_activate_map.start_lon),
        zoom: dbmng_activate_map.start_zoom
    });
		map.invalidateSize();

    var mapquestUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png',
    subDomains = ['otile1','otile2','otile3','otile4'],
    mapquestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors.';
    var mapquest = new L.TileLayer(mapquestUrl, {maxZoom: 18, attribution: mapquestAttrib, subdomains: subDomains});
    map.addLayer(mapquest); 


		var marker = L.marker([lat, lon]).addTo(map); 
		
	}

}

dbmng_getPosition  = function(id_element){

	console.log("getPosition");
	jQuery("#"+id_element+"_label").html('Searching...');
	console.log(id_element);

	if(is_cordova()){

		var opt={ enableHighAccuracy: true };

		navigator.geolocation.getCurrentPosition(
			function(position) {
				coord="POINT( " +position.coords.longitude+" "+ position.coords.latitude + ") ";

				jQuery("#"+id_element+"_label").html('Coordinates Found :<a href="geo:'+ position.coords.latitude + ','+position.coords.longitude+'">'+coord+"</a> Accuracy: "+position.coords.accuracy);
				jQuery("#"+id_element).val(coord);
			},                                         
			function(error) {
				console.log(error);
				alert(error.message);
			}, 
      opt
		);

	}
	else{	
	 if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position){
						coord="POINT( " +position.coords.longitude+" "+ position.coords.latitude + ")";
 				    jQuery("#"+id_element+"_label").html('Coordinates Found :'+coord);

						jQuery("#"+id_element).val(coord);

				});
    } 
		else {
       alert("Geolocation is not supported by this browser.");
    }
	}

	
}


 
function uploadPhoto(imageURI) {

	debug("imageURI: "+imageURI);
	var o=jQuery.jStorage.get('tmp_picture');
	o['imageURI']=imageURI;
	jQuery.jStorage.set('tmp_picture',o);

	var img={'imageURI': imageURI, 'uploaded': 0};

	jQuery('#'+o['obj_id']+'_'+o['id_record']+'_'+o['fld']).val(JSON.stringify(img));
	jQuery('img#'+o['obj_id']+'_'+o['id_record']+'_'+o['fld']+'_image').attr("src",imageURI);

	
	
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
