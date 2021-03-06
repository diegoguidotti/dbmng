

jQuery(document).ready(function() {
	


	for(n=0; n<acronyms.length; n++){

		var a=acronyms[n];
		//console.log(a);

		
		if( jQuery('#content div.field-name-body').html() )
		{
			var re = new RegExp(" "+a.acronym+" ","g");
			jQuery('#content div.field-name-body').html(jQuery('#content div.field-name-body').html().replace(re,' <span class="acronym" short_desc="'+a.acronym+'" long_desc="'+a.description+'">'+a.acronym+'</span> '));


			var re2 = new RegExp(" "+a.acronym+"s ","g");
			jQuery('#content div.field-name-body').html(jQuery('#content div.field-name-body').html().replace(re2,' <span class="acronym" short_desc="'+a.acronym+'s" long_desc="'+a.description+'s">'+a.acronym+'s</span> '));
		}
	}



	jQuery('span.acronym').click(function(){
		var s=jQuery(this);
		
		var lon=s.attr('long_desc');
		var sho=s.attr('short_desc');
		if(s.html()==sho){
			s.html(lon+'');
		}
		else{
			s.html(sho);
		}
		//alert(t);
	});


	jQuery('.carousel').carousel()

});


//draw a leaflet maps containing data
function climasouth_leaflet(data, aForm, aParam){
	cs_mapResize();
	jQuery('#label_container').hide();


	var coord=[27.9, 9];
	//if(aParam.coord)
	//	coord=aParam.coord;

	var zoom=3;
	if(aParam.zoom)
		zoom=aParam.zoom;

	jQuery('#page-title').show();
	var base_path=aParam.base_path;


    var southWest = L.latLng(10, -40),
    northEast = L.latLng(44, 45),
    bounds = L.latLngBounds(southWest, northEast);


	//create the map objet
  var map = L.map(aParam.div_element,{zoomControl: true, minZoom: 4, maxZoom: 10, maxBounds: bounds}).setView(coord, zoom);

/*
		var mapquestUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png',
            subDomains = ['otile1','otile2','otile3','otile4'],
            mapquestAttrib = 'Data, imagery and map information provided by <a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors.';
            var mapquest = new L.TileLayer(mapquestUrl, {maxZoom: 18, attribution: mapquestAttrib, subdomains: subDomains});
      map.addLayer(mapquest);  
*/
		var mapquest = new  L.tileLayer('http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
			});

      map.addLayer(mapquest);  



	//add a background layer	
	var back_map = L.tileLayer.wms("http://95.225.174.45/geoserver_ae/wms", {
	  layers: 'climasouth:countries',
	  format: 'image/png',
//	  'BGCOLOR': '0xcfeaf3'					  			
	  'BGCOLOR': '0xf4f3ed'
		
					  			
	});
	back_map.setOpacity(0.6);
	map.addLayer(back_map); 	


//debug(map.getBounds());	
/*
	var imageUrl = 'http://www.climasouth.eu/drupal/sites/all/modules/climasouth/resources/map4.png';
  L.imageOverlay(imageUrl, map.getBounds()).addTo(map);
*/


/*
	map.dragging.disable();
	map.touchZoom.disable();
	map.doubleClickZoom.disable();
	map.scrollWheelZoom.disable();
*/


	
	var html = "<div class='climasouth_popup'>";
		html +='<div class="cs_left"><h3>'+Drupal.t('South Mediterranean Region')+'</h3></div>';
		html += '<div class="cs_right"><b>'+Drupal.t('N. of countries')+':</b><br/>9<br/><b>'+Drupal.t('Population (millions)')+':</b><br/> 212.4<br/><b>'+Drupal.t('GDP ($ billions)')+':</b><br/> 694.2<br/><b>'+Drupal.t('GDP per capita')+' ($):</b><br/> 3665<br/></div></div>';
		jQuery('#map_info_container').html(html);




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

			var hdi='n/a';
			var population='n/a';
			var gdp='n/a';
			var gdp_pc='n/a';

			try{
				var data= jQuery.parseJSON(v.html_content);
				if(data.hdi)
					hdi=data.hdi.value+" ("+data.hdi.year+")";
				if(data.population)
					population=data.population.value+" ("+data.population.year+")";
				if(data.gdp)
					gdp=data.gdp.value+" ("+data.gdp.year+")";
				if(data.gdp_pc)
					gdp_pc=data.gdp_pc.value+" ("+data.gdp_pc.year+")";
			}
			catch(e){
				;
			}


			var html = "<div class='climasouth_popup'>";
			html +='<div class="cs_left"><img class="fla" src="'+base_path+v.flag+'"/><h3>';
			//html+='<a href="'+base_path+'climasouth/country?id_c_country='+v.id_c_country+'">';
			html+=v.country_name;
			//html+='</a>';
			html+='</h3></div>';
			html += '<div class="cs_right"><b>'+Drupal.t('Population (millions)')+':</b><br/>'+population+'<br/><b>'+Drupal.t('GDP ($ billions)')+':</b><br/>'+gdp+'<br/><b>'+Drupal.t('GNI per capita')+' ($):</b><br/>'+gdp_pc+'<b><br/>'+Drupal.t('Human Development Index')+':</b><br/>'+hdi+'</div></div>';

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
var open_tile;

function formatTileResources(){

	//enlarge the container
	jQuery('#sidebar-first').hide();
	jQuery('#content').width(1200);
	jQuery('#map_info_container').hide();

	//jQuery('#main-top').show()


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


	//navigate throush all resources
	jQuery('.climasouth_res').click(function(){

		if(open_tile){
			open_tile.width(220);
			open_tile.height(250);
			open_tile.children('div.climasouth_res_header').width(220);
		}

		
		var t=jQuery(this);
		if(!t.is(open_tile)){
			t.width(461);
			t.height(520);
			t.children('div.climasouth_res_header').width(461);

			open_tile=t;
		}
		else{
			open_tile=null;
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
		//console.log(text_tile);
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

					if(cls!='author'){

						var vvv=txt.split('<span>,</span>');
					
						for(var n=0; n<vvv.length; n++ ){	

							var val_ok=vvv[n].trim();	

							if(tags[cls+'|'+val_ok]){
								tags[cls+'|'+val_ok]=tags[cls+'|'+val_ok]+1;
							}
							else{
								tags[cls+'|'+val_ok]=1;

								//console.log('add tag |'+cls+'|'+vvv[n]+' '+vvv);
							}
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
		var sel='';

		sel+='<select id="tag_sel_country"><option>'+Drupal.t('Choose a country')+'</option></select>';
		sel+='<select id="tag_sel_tags"><option>'+Drupal.t('Choose a Tag')+'</option></select>';
		sel+='<select id="tag_sel_subject"><option>'+Drupal.t('Choose a Subject')+'</option></select>';
		sel+='<select id="tag_sel_language"><option>'+Drupal.t('Choose a Language')+'</option></select>';
		sel+='<select id="tag_sel_year"><option>'+Drupal.t('Choose a Year')+'</option></select>';
		sel+='<select id="tag_sel_file_format"><option>'+Drupal.t('Choose a file format')+'</option></select>';

		jQuery("#tag_sel_container").html(sel);

		jQuery("#tag_sel_container select").change(function(){
			var s=jQuery(this);
			addToSearchItem(s.val());
		});

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

			console.log(k);
			if(jQuery('#tag_sel_'+cls)){					
					jQuery('#tag_sel_'+cls).append('<option value="'+val+'">'+val+'</option>');
			}

			//il minimo 5, il massimo 20 decide le dimensioni del tag
			var size=Math.round(8+ (dim/scarto)*30);		
						
			html+='<div style="font-size: '+(size)+'px;" class="tag '+cls+'">'+val+'</div>';

		}

		var searched='<div id="searched_tags">';
		if(search_tag.length>0){
			searched+='<div  class="searched_label">'+Drupal.t('Resources has been filtered by')+': </div>'
			for(n=0; n<search_tag.length; n++){
				searched+='<div class="searched">'+search_tag[n]+'<span ><sup>X</sup></span></div>';
			}
		}
		searched+="</div>";
		
		//console.log(html);
		jQuery('#tag_container').html('<div id="tag_cloud">'+html+"</div>"+searched);

		jQuery('#tag_cloud div.tag').click(function (k,v){
			var t=jQuery(this);
			addToSearchItem(t.text());

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


function addToSearchItem(txt){
		if(jQuery.inArray(txt, search_tag)==-1){
			search_tag.push(txt);
			searchTile('');
		}
}

function climasouth_no_map(){

	

	if(jQuery('#main-nav ul.menu>li.last').hasClass('active-trail')){
		jQuery('#main-top').hide();
		jQuery('#sidebar-first').hide();
		jQuery('#content').width(1200);
	}

	jQuery("div.field-name-field-image").hide();
	jQuery("div.field-name-field-video").hide();
	jQuery("div.field-name-field-label").hide();

	var src = jQuery("div.field-name-field-label div.field-items div.field-item").html();
	if( typeof src != 'undefined' && src != '' && src != null && src != 'null' ){
		jQuery('#label_container').show();
		jQuery('#label_container').html('<span style="vertical-align: middle;">'+src+'</span>');
	}

	var no_media=true;

	var src=jQuery("div.field-name-field-image img").attr('src');
	if( typeof src != 'undefined' ){
		html = "<div class='cs_image_cropper'><img src='"+src+"'></div>";
		jQuery('#map_info_container').hide();
		//jQuery('#map_container').css('background-image',"url('"+src+"')");
		jQuery('#map_container').html(html);
		jQuery('#map_container').css('background', "#f3f3f3");
		jQuery('#map_blue_left').css('background', "#f3f3f3");

		no_media=false;
	}
	else{
		var color = "71b1cb";

		var src = jQuery("div.field-name-field-video div.field-items").text();
		if( typeof src != 'undefined' && src != '' ){
			//alert("climasouth_no_map video");
			html = '<iframe src="//'+src+'?color='+color+'&badge=0&title=0&byline=0&portrait=0" width="553" height="311" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'; 
			jQuery('#map_info_container').hide();
			jQuery('#map_container').html(html);
			jQuery('#map_container iframe').css('margin-left','318px');
			jQuery('#map_container').css('background', "#f3f3f3");
			jQuery('#map_blue_left').css('background', "#f3f3f3");
			no_media=false;
		}
	}



	if(no_media){
		jQuery('#cs_top_banner').hide();
	}

	cs_mapResize();

}

//lancia il reside della mappa ogni volta che si modifica
jQuery( window ).resize(function() {
	cs_mapResize();
});

//resize della mappa
function cs_mapResize(){
	//var mh=jQuery(window).height()-80;
	var mw=jQuery(window).width();

	jQuery('#toolbar').width(mw);
	jQuery('#header').width(mw);
	jQuery('#main').width(mw);
	jQuery('#main-menu').width(mw);
	jQuery('#footer').width(mw);

	jQuery('#climasouth_toolbar').width(1170);

	jQuery('#main-menu div.navbar').width(1170);
	jQuery('#header div.span12').width(1170);
	jQuery('#main-top').width(1170);

	var pos=jQuery('#main-top').offset().left;
	if(pos<20){
		pos=20;
	}

	console.log(pos);
  
  //jQuery("#map_container").width(mw-300-(2*pos)); 
  jQuery("#map_container").width(870); 

	jQuery("#map_blue_left").css('margin-left',-(pos));
	jQuery("#map_blue_left").width((pos));

	jQuery("#map_new_container").css('margin-right',-(pos-10));
  jQuery("#map_new_container").width(pos+290); 
	jQuery("#map_new_container").css('padding-right',(10));


	debug(jQuery('#main-menu div.navbar').width());

	//JS fix for arabic
	if(jQuery('body').css('direction')=='rtl'){
		jQuery('#map_container').css('left','-300px');
		//jQuery('#climasouth_news').css('padding-right','150px');
		jQuery('#map_container iframe').css('position', 'absolute');
		jQuery('#map_container iframe').css('left', '0px');
	  jQuery('#map_container .cs_image_cropper').css('position', 'absolute');
		jQuery('#map_container .cs_image_cropper').css('left', '0px')
    jQuery('#home_activities h4.ha').css('margin-right','300px');
		jQuery('.carousel-inner iframe').css('margin-right','310px');
		jQuery('.carousel-inner .cs_image_cropper').css('margin-right','310px');
		jQuery('#myCarousel .right').html('‹');
		jQuery('#myCarousel .left').html('›');
	}

	var logo=jQuery('#logo img').attr('src');
	var lang=jQuery('html').attr('lang')
	var new_logo=logo.substring(0,logo.length-4)+'_'+lang+'.png';
	jQuery('#logo img').attr('src',new_logo);

	try{
		if((jQuery('h1#page-title').html()).trim()=='لمحة عامة والبيانات الرئيسية'){
			if(jQuery('html').attr('lang')=='en'){
				jQuery('h1#page-title').html('Overview & key data');
			}
			else if(jQuery('html').attr('lang')=='fr'){
				jQuery('h1#page-title').html('Données de base');
			}
		}
	}
	catch(e){
		;
	}


	//if(mw<700){
	//		jQuery('#logo img').width(400);
	//}
	//else {
	//}

	var mobile=false;
		if(mobile){
			jQuery('#main-nav ul').hide();


			html='';

				  html+='<div class="navbar"><div class="navbar-inner"><div class="container">';

						html+='<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">        <span class="icon-bar"></span>        <span class="icon-bar"></span>        <span class="icon-bar"></span> </a>'

						html+='    ';
						html+='<div class="nav-collapse collapse">'
							html+=' <ul class="nav" >';
						
							jQuery.each(jQuery('#main-nav > ul > li'), function(k,v){

								//console.log(v);
								v=jQuery(v);

								a=v.children('a');

								sub=v.children('ul');

								var cl='';
								var at=''
								if(sub.length>0){
									cl+='dropdown'
									at+=' class="dropdown-toggle" data-toggle="dropdown"';
								}
								
								html+='<li class="'+cl+'"><a '+at+' href="'+a.attr('href')+'">'+a.text()+'</a>';
								//console.log(sub);

								if(sub.length>0){
									html+='<ul class="dropdown-menu">';

									jQuery.each(jQuery(sub).children('li'), function(k,sa){
										sa=jQuery(jQuery(sa).children('a'));	
										html+='<li><a href="'+sa.attr('href')+'">'+sa.text()+'</a></li>';
									});
									html+='</ul>';
								}
				
								html+='</li>';

							});
							//Start menu

								html+='      <li class="active"><a href="#">Home</a></li>';

								html+='<li class="dropdown"><a   href="#">Link</a>';

									html+='                        <ul class="dropdown-menu"><li><a href="#">Action</a></li><li><a href="#">Another action</a></li>                        </ul>';
								html+='</li>';

								html+='<li><a href="#">Link</a></li>';

							//End menu

							html+='</ul>';
					html+='</div></div></div></div>';
 
   



/*
			html='<nav class="navbar navbar-default" role="navigation"><div class="container-fluid">';
    		html+='<div class="navbar-header">';
      		html+='<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>';

      	html+='<a class="navbar-brand" href="#">Brand</a>    </div>';

			html+='</div></div>'
*/

			jQuery('#main-nav').html(html);
			jQuery('#cs_top_banner').hide();

			jQuery('#main-menu ul').css('background-color', '#DD6659');

			jQuery('#main-menu li').css('background-color', '#DD6659');


		}
		else{

			//jQuery('#cs_top_banner').show();

	}


}


String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};



