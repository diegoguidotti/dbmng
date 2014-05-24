

//draw a leaflet maps containing data
function climasouth_leaflet(data, aForm, aParam){

	jQuery('#label_container').hide();
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
			//var geo = JSON.parse(v.geojson);
			var geo = jQuery.parseJSON(v.geojson);
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

var tags;
var search_tag;

function formatTileResources(){

	//enlarge the container
	jQuery('#sidebar-first').hide();
	jQuery('#content').width(1200);
	jQuery('#map_info_container').hide();

	search_tag=new Array();

	//scan all resources to generate the tagcloud
	searchTile('');

	jQuery("#free_search").keydown(function(){
         var val = jQuery(this).val().trim();
         //val = val.replace(/\s+/g, '');
					val=val.toLowerCase();
					//console.log(val.length);

         if(val.length >= 1) { //for checking 3 characters

							searchTile(val);

         }
				else{
					jQuery('.climasouth_res').show();
					searchTile('');
				}
    });     


}


function searchTile(val){

	tags=new Array();

	//navigate throush all resources
	jQuery.each(jQuery('.climasouth_res'), function(k,v){
		v=jQuery(v);
		//console.log('res_'+k);

		//activated tile that follow the search
		var use_tile=true;
		var text_tile=v.text().toLowerCase();
		
		if(search_tag.length>0){
			//console.log(search_tag);
			
			use_tile=true;
			for(n=0; n<search_tag.length; n++){
				if(text_tile.indexOf(search_tag[n].toLowerCase()) ==-1){
					use_tile=false;
				}
				//console.log(text_tile+' contains '+search_tag[n]+' '+use_tile);

			}
		}

		if(use_tile){
			//console.log(text_tile);
			//console.log(val);
			if(text_tile.indexOf(val) > -1){
				use_tile=true;
			}
			else{
				use_tile=false;
			}
		}

		if(use_tile){
			v.show();

			vars=v.children('div.climasouth_res_vars').children();
			jQuery.each(vars, function(k2, v2){
					v2=jQuery(v2);
					cls=v2.attr('class');
					txt=v2.children('span.cvalue').html();

					var vvv=txt.split('<span>,</span>');
				
		
					
					for(var n=0; n<vvv.length; n++ ){	
						if(tags[cls+'|'+vvv[n]]){
							tags[cls+'|'+vvv[n]]=tags[cls+'|'+vvv[n]]+1;
						}
						else{
							tags[cls+'|'+vvv[n]]=1;

							//console.log('add tag |'+cls+'|'+vvv[n]+' '+vvv);
						}
					}
			});

		}
		//disactivate other tiles
		else{
			
			v.hide();
		}
	});

	createTagCloud();

}

	function createTagCloud(){
		
		var html='';

		var min=1;
		var max=1;
		for (var k in tags) {
			min=Math.min(min,tags[k]);
			max=Math.max(max,tags[k]);
		}
		var scarto=max-min;
		if(scarto<=0) {
			scarto=1;
		}

		for (var k in tags) {			
			var cls=k.split('|')[0];			
			var val=k.split('|')[1];			
			var dim=tags[k];	

			//il minimo 5, il massimo 20 decide le dimensioni del tag
			var size=Math.round(8+ (dim/scarto)*30);		
						
			html+='<div style="font-size: '+(size)+'px;" class="tag '+cls+'">'+val+'</div>';

		}

		var searched='<div id="searched_tags">';
		if(search_tag.length>0){
			searched+='<div class="searched_label">Resources has been filtered by: </div>'
			for(n=0; n<search_tag.length; n++){
				searched+='<div class="searched">'+search_tag[n]+'<span ><sup>X</sup></span></div>';
			}
		}
		searched+="</div>";
		
		//console.log(html);
		jQuery('#map_container').html('<div id="tag_cloud">'+html+"</div>"+searched);

		jQuery('#tag_cloud div.tag').click(function (k,v){
			var t=jQuery(this);
			

			if(jQuery.inArray(t.text(), search_tag)==-1){
				search_tag.push(t.text());
				searchTile('');
			}
		});

		jQuery('#searched_tags div.searched').click(function (k,v){
			var t=jQuery(this);
			var txt=t.text();

			//remove the last letter (te X to delete)
			txt=txt.substring(0,txt.length-1);

			var pos=jQuery.inArray(txt, search_tag);


			search_tag.splice(pos,1);
			searchTile('');
		});

	}


function climasouth_no_map(){

	
	cs_mapResize();


	jQuery("div.field-name-field-image").hide();
	jQuery("div.field-name-field-video").hide();
	jQuery("div.field-name-field-label").hide();

	var src = jQuery("div.field-name-field-label div.field-items div.field-item").html();
	if( typeof src != 'undefined' && src != '' && src != null && src != 'null' ){
		jQuery('#label_container').show();
		jQuery('#label_container').html('<span style="vertical-align: middle;">'+src+'</span>');
	}


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
		if( typeof src != 'undefined' && src != '' ){
			//alert("climasouth_no_map video");
			html = '<iframe src="//'+src+'?color='+color+'" width="553" height="311" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'; 
			jQuery('#map_info_container').hide();
			jQuery('#map_container').html(html);
			jQuery('#map_container iframe').css('margin-left','318px');
			jQuery('#map_container').css('background', "#f3f3f3");
			jQuery('#map_blue_left').css('background', "#f3f3f3");
		}
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


