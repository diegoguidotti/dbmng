

//draw a leaflet maps containing data
function climasouth_leaflet(data, aForm, aParam){


	cs_mapResize();

	var coord=[27.9, 9];
	//if(aParam.coord)
	//	coord=aParam.coord;

	var zoom=3;
	if(aParam.zoom)
		zoom=aParam.zoom;


	var base_path=aParam.base_path;

	//create the map objet
  var map = L.map(aParam.div_element,{zoomControl: false}).setView(coord, zoom);

	//add a background layer	
	var back_map = L.tileLayer.wms("http://95.240.35.64/geoserver_ae/wms", {
	  layers: 'climasouth:countries',
	  format: 'image/png',
	  'BGCOLOR': '0xcfeaf3'					  			
	});
map.addLayer(back_map); 	


debug(map.getBounds());	

	var imageUrl = 'http://www.climasouth.eu/drupal/sites/all/modules/climasouth/resources/map4.png';
  L.imageOverlay(imageUrl, map.getBounds()).addTo(map);


	map.dragging.disable();
	map.touchZoom.disable();
	map.doubleClickZoom.disable();
	map.scrollWheelZoom.disable();

	




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
			html +='<div class="cs_left"><img class="fla" src="'+base_path+v.flag+'"/><h3><a href="'+base_path+'climasouth/country?id_c_country='+v.id_c_country+'">'+v.country_name+'</a></h3></div>';
			html += '<div class="cs_right"><b>Total Area:</b><br/> xxx m2<br/><b>Population:</b><br/> xxxx<br/><b>GDP per capita:</b><br/> xxxx</div></div>';

			//html += '<a hrgef="'+base_path+'climasouth/country?id_c_country='+v.id_c_country+'">'+Drupal.t('go to page')+'</a></div>';
			//feature.bindPopup(html);

			feature.on('click', function(evt) {

				jQuery('#map_info_container').html(html);
				 
			});


			feature.addTo(map);
		}
	});	
}


function climasouth_no_map(){

	
	cs_mapResize();


	jQuery("div.field-name-field-image").hide();
	jQuery("div.field-name-field-video").hide();
	
	var src=jQuery("div.field-name-field-image img").attr('src');
	if( typeof src != 'undefined' ){
		html = "<div class='cs_image_cropper'><img src='"+src+"'></div>";
		jQuery('#map_info_container').hide();
		//jQuery('#map_container').css('background-image',"url('"+src+"')");
		jQuery('#map_container').html(html);
		jQuery('#map_container').css('background', "#f3f3f3");
		jQuery('#map_blue_left').css('background', "#f3f3f3");
	}
	else{
		var color = "71b1cb";
		var src = jQuery("div.field-name-field-video div.field-items").text();
		html = '<iframe src="//'+src+'?color='+color+'" width="553" height="311" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'; 
		jQuery('#map_info_container').hide();
		jQuery('#map_container').html(html);
		jQuery('#map_container iframe').css('margin-left','318px');
		jQuery('#map_container').css('background', "#f3f3f3");
		jQuery('#map_blue_left').css('background', "#f3f3f3");
	}

}

//lancia il reside della mappa ogni volta che si modifica
jQuery( window ).resize(function() {
	cs_mapResize();
});

//resize della mappa
function cs_mapResize(){
	//var mh=jQuery(window).height()-80;
	var mw=jQuery(window).width();

	var pos=jQuery('#main-top').offset().left;

	debug(pos);
  
  jQuery("#map_container").width(mw-300-(2*pos)); 

	jQuery("#map_blue_left").css('margin-left',-(pos));
	jQuery("#map_blue_left").width((pos));

	jQuery("#map_new_container").css('margin-right',-(pos-10));
  jQuery("#map_new_container").width(pos+290); 
	     

}


