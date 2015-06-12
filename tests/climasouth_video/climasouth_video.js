
jQuery(function(){

		
		jQuery("#cs_short").prop('muted', true);
			/*
		video=jQuery("#cs_short").get(0);

		video.addEventListener('click',function(){
			video.play();			
			jQuery("#cs_short").prop('muted', true);
		});

		video.addEventListener('canplay', function() {
			video.play();
			jQuery("#cs_short").prop('muted', true);
		});

		video.load();
		video.play();
    jQuery("#cs_short").prop('muted', true);
*/


		resize();

		jQuery( window ).resize(function() {
			resize();
		});		

		if(typeof fill_the_form != 'undefined' ){
			if(fill_the_form){
				fillTheForm();
			}
		}

		

		jQuery('.diego_editable').click(function(){

			console.log('click');

			var v=jQuery(this);
			var text=v.html();
			if(!text.indexOf('<input')==0){
				console.log(text);

				var ww= v.width();

				var html='<input />';
				v.html(html);

				var input=jQuery(v.children('input'));

				//console.log('resize input at '+ww+'px');
				input.css('width',ww+'px');

				input.focus().val(text);
				
			}
			else{
			}

		});

		jQuery('.diego_editable').focusout(function(){

			console.log('blur');

			var v=jQuery(this);
			var text=v.html();
			if(text.indexOf('<input')==0){
				html=jQuery(v.children('input')).val();
				if(html.trim()=='') html='...';
				v.html(html);
			}
		});


		jQuery('.diego_editable').keyup(function(event){

			console.log('keyup'+event.which);

			var v=jQuery(this);
			var input=v.children('input');

			if(event.which==13){
				html=jQuery(input).val();
				if(html.trim()=='') html='...';
				v.html(html);
			}
			else{				
				
				var l=input.val().length;
				input.css('width',(10 * input.val().length)+'px');
			}
		});

		if(jQuery('#video_list').length){
			showAllVideos();
		}


});


function resize(){
   
		
		var vh=jQuery('#intro video').height();

		var tm=((vh)/2);

		if(vh<600)
			tm=0;
		//console.log('Video alto:'+vh+" metto margine: -"+tm);
		jQuery('#intro video').css('margin-top','-'+tm+'px');

		try{
			var left=jQuery('#about div.row').offset().left;
			jQuery('#intro #cs_video_title').css('left', left+'px');
		}
		catch(e){;}


		min_height=jQuery(window).height();
		win_width=jQuery(window).width();
		if(min_height<400)
			min_height=400;

		
		jQuery.each(jQuery('section'), function(){
			var s=jQuery(this);

			
			s.css('height','auto');
			auto_height=s.height();

			
			var h=Math.max(min_height,auto_height)+200;
			s.css('height',h+'px');

			//console.log(s.attr('id')+" "+h);
		});

	


			//use the boootstrap menu
			if(jQuery('ul.nav').width()==0){
				jQuery('#cs_logo').hide();
				jQuery('.cs_head a.navbar-brand').width(win_width-100);
				jQuery('.cs_head a.navbar-brand').css('height','auto');
			}
			else{
				jQuery('#cs_logo').show();
				jQuery('.cs_head a.navbar-brand').width(jQuery('#upload h1').width()-350);
				jQuery('.cs_head a.navbar-brand').css('height','auto');
				//jQuery('.cs_head a.navbar-brand').css('border','1px solid red');

			
	}
}


function saveVideo(){

	var obj={'ajax':true};
	
	jQuery.each(jQuery('#videoForm :input'), function(k,v){
		v=jQuery(v);
		obj[v.attr('id')]=v.val();		
	});

	if(jQuery('#inputAccept').is(':checked')){

		jQuery.ajax({
		      type: "POST",
		      url: 'video',
					DataType: 'json',
		      data: obj,                
		      success: function(data){
		          jQuery('#update_modal .csv_ok').show();
		          jQuery('#update_modal .csv_err1').hide();
		          jQuery('#update_modal .csv_err2').hide();

							jQuery('#update_modal').modal('show');
					},
		      error: function(errMsg) {
		          jQuery('#update_modal .csv_ok').hide();
		          jQuery('#update_modal .csv_err1').hide();
		          jQuery('#update_modal .csv_err2').show();
							jQuery('#update_modal').modal('show');        
					}
		});
	}
	else{
		          jQuery('#update_modal .csv_ok').hide();
		          jQuery('#update_modal .csv_err1').show();
		          jQuery('#update_modal .csv_err2').hide();
							jQuery('#update_modal').modal('show');        
	}

	return false;
}


function fillTheForm(){

	jQuery(function(){

		var obj={'ajax':true, 'get_video':true};

		jQuery.ajax({
			    type: "POST",
			    url: 'video',
					DataType: 'json',
			    data: obj,                
			    success: function(data){
						
						console.log(data);
						jQuery.map(data.ret, function(element,index) {
							if(index.substring(0,5)=='input'){
								console.log(index);
								jQuery('#'+index).val(data.ret[index]);
							}	
						});
						
					}
		});
	});
}



function showAllVideos(){

	jQuery(function(){
		html='<h3>Video</h3>';



var obj={'ajax':true, 'get_all_video':true};

		jQuery.ajax({
			    type: "POST",
			    url: 'video',
					DataType: 'json',
			    data: obj,                
			    success: function(data){
						
						console.log(data);
						
						jQuery.each(data.ret, function(k,val) {
							console.log(val);
							if(val.inputAccept){
								html+='<div class="video_box">';
									html+='<h3>'+val.inputTitle+"</h3>";
									html+='<div class="video_item"><span>Email</span>: '+val.inputEmail+"</div>";
									html+='<div class="video_item"><span>Author</span>: '+val.inputAuthor+"</div>";
									html+='<div class="video_item"><span>Video</span>: <a target="_NEW" href="'+val.inputURL+'">'+val.inputURL+'</a></div>';
									html+='<div class="video_item"><span>Citizenship</span>: '+val.inputCitizenship+"</div>";
									html+='<div class="video_item"><span>Extended Title</span>: '+val.inputTitleExtended+"</div>";
									html+='<div class="video_item"><span>Address</span>: '+val.inputAddress+"</div>";
									html+='<div class="video_item"><span>Description</span>: '+val.inputDescription+"</div>";
								html+='</div>';		
							}
						});




						jQuery('#video_list').html(html);
						resize();
						
					}
		});




	});
	
}

function uploadVideo(){
	var input=jQuery('#cs_video_link').val();

	link='';
	error_message='';
	if(input.indexOf('vimeo.com/')>-1){
		

		link='//player.vimeo.com/video/'+ input.substring(input.indexOf('vimeo.com/')+10, input.length)  +'?color=71b1cb&amp;badge=0';
	}
	else if(input.indexOf('youtube.com/watch?v=')>-1){
		link='//www.youtube.com/embed/'+ input.substring(input.indexOf('youtube.com/watch?v=')+20, input.length)  +'';
	}
	else if(input==''){
	}
	else {
		error_message=('Unknown video format. Please use the following examples:<ul><li>youtube: https://www.youtube.com/watch?v=6nMVZWtcFHw</li><li>Vimeo: https://vimeo.com/104943503</li></ul>');
	}

	jQuery('#cs_video_container').html('');
	if(link!=''){
		jQuery('<iframe src="'+link+'" id="myFrame" name="myFrame" width="553" height="311" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" >').appendTo('#cs_video_container');
	}
	else{
		jQuery('#cs_video_container').html(error_message);
	}
}


var myRadarChart;

var group={};

function createSpiderChart(populate_select){


	var obj={'ajax':'ok', 'spider': ''};
	jQuery.ajax({
		      type: "POST",
		      url: base_path+'/climasouth/video',
					DataType: 'json',
		      data: obj,                
		      success: function(data){
						//console.log(data);
	
						group={};
						jQuery.each(data.res.data, function(k,v){
							//console.log(v+" "+jQuery.inArray(v[0], group));
							//console.log(group);
							if(!group[v[0]]){
								group[v[0]]={"name": v[1]+" - "+v[2], "simulation":{}};
							}

							if(!group[v[0]].simulation[v[3]]){
								group[v[0]].simulation[v[3]]={};
							}
						
							group[v[0]].simulation[v[3]][v[4]]={'current': v[5] ,'target':v[6]}
						});

						console.log(group);
						if(populate_select)
							populateSelect();
						else
							updateChart();	
					},
		      error: function(errMsg) {
						console.log(errMsg);
					}
		});
}

function populateSelect(){

	
	
	var opt='';
	jQuery.each(group, function(k,v){
		opt+='<option selected value="'+k+'">'+v.name+'</option>';
	});
	jQuery('#choose_a_sector').html(opt);
	changeSector();
}

function changeSector(){


	var sec=jQuery('#choose_a_sector').val();

	console.log("Choose sec "+sec);

	var opt='';
	jQuery.each(group[sec].simulation, function(k,v){
		console.log('ac'+k)
		opt+='<option selected value="'+k+'">'+k+'</option>';
	});
	jQuery('#choose_a_simulation').html(opt);
	changeSimulation();
	
}


function changeSimulation(){
	//console.log('plot '+sec+" "+cou);
	spider_select();
}


function updateChart(){
	var sec=jQuery('#choose_a_sector').val();
	var cou=jQuery('#choose_a_simulation').val();

	var vals= group[sec].simulation[cou];
	var labels=[];
	var labelsLong=[];
	var data_target=[];
	var data_current=[];

	jQuery.each(vals, function(k,v){

		var l=k.substr(0,40);
		if(l.length==40) l+="...";
		labels.push(l);		
		labelsLong.push(k);		
		//labels.push(k.substr(0,40));		


		data_target.push(v.target);
		data_current.push(v.current);
	});

	createSpiderChart2(labels, labelsLong, data_target,  data_current);
}


function createSpiderChart2(labels, labelsLong, data_target,  data_current){

	var data = {
		  labels: labels,
		  datasets: [
		      {
		          label: "Current Rating",
		          fillColor: "rgba(255,0,0,0.3)",
		          strokeColor: "rgba(255,0,0,1)",
		          pointColor: "rgba(255,0,0,1)",
		          pointStrokeColor: "#fff",
		          pointHighlightFill: "#fff",
		          pointHighlightStroke: "rgba(255,0,0,1)",
		          data: data_current
		      },
		      {
		          label: "Target Rating",
		          fillColor: "rgba(0,0,255,0.3)",
		          strokeColor: "rgba(0,0,255,1)",
		          pointColor: "rgba(0,0,255,1)",
		          pointStrokeColor: "#fff",
		          pointHighlightFill: "#fff",
		          pointHighlightStroke: "rgba(0,0,255,1)",
		          data:  data_target
		      }
		  ]
			,labelsLong: labelsLong

	};

	var hh='<canvas id="spider_chart" width="900" height="550"></canvas><div id="spider_legends"><div id="spider_chart_legend"></div>';
	hh+='<table class="rating_legend"><tr><th>1</th><td>Unsatisfactory: No substantial targets or benefits achieved.</td></tr><tr><th>2</th><td>Moderately Unsatisfactory: Major shortcomings and limited relevance.</td></tr><tr><th>3</th><td>Moderately Satisfactory: Limited compliance with key targets, significant shortcomings and/or modest overall relevance.</td></tr><tr><th>4</th><td>Satisfactory: In substantial compliance with key targets, with only minor shortcomings that are subject to remedial action.</td></tr> <tr><th>5</th><td>Highly Satisfactory. Targets achieved or exceeded, without shortcomings. Can be presented as “good” practice</td></tr></table></div>';

	jQuery('#radar_container').html(hh);

	if(false){

		
		myRadarChart.datasets=data;
		myRadarChart.update();
	}
		else{


			// Get the context of the canvas element we want to select
		var ctx; // = document.getElementById("spider_chart").getContext("2d");

		//var cv = document.getElementById("cv");

		try{
			var canvas = document.getElementById("spider_chart");
		  ctx = canvas.getContext('2d');            

		



				var options={
						//Boolean - Whether to show lines for each scale point
						scaleShowLine : true,

						//Boolean - Whether we show the angle lines out of the radar
						angleShowLineOut : true,

						//Boolean - Whether to show labels on the scale
						scaleShowLabels : false,

						// Boolean - Whether the scale should begin at zero
						scaleBeginAtZero : true,

						//String - Colour of the angle line
						angleLineColor : "rgba(0,0,0,.1)",

						//Number - Pixel width of the angle line
						angleLineWidth : 1,

						//String - Point label font declaration
						pointLabelFontFamily : "'Arial'",

						//String - Point label font weight
						pointLabelFontStyle : "normal",

						//Number - Point label font size in pixels
						pointLabelFontSize : 15,

						//String - Point label font colour
						pointLabelFontColor : "#666",

						//Boolean - Whether to show a dot for each point
						pointDot : true,

						//Number - Radius of each point dot in pixels
						pointDotRadius : 6,

						//Number - Pixel width of point dot stroke
						pointDotStrokeWidth : 1,

						//Number - amount extra to add to the radius to cater for hit detection outside the drawn point
						pointHitDetectionRadius : 20,

						//Boolean - Whether to show a stroke for datasets
						datasetStroke : true,

						//Number - Pixel width of dataset stroke
						datasetStrokeWidth : 2,

						//Boolean - Whether to fill the dataset with a colour
						datasetFill : true,

						//String - A legend template
						legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\">&nbsp;</span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",				

				};

	
		
			myRadarChart = new Chart(ctx).Radar(data, options);
			var legend = myRadarChart.generateLegend();

			//and append it to your page somewhere
			jQuery('#spider_chart_legend').append(legend);
		}
		catch(e){
			jQuery('#radar_container').append("<div class='error'>You need to update your browser to the latest version.</div>");

		}
	}

}


function spider_select()
{
	


		id='spider_ind_container';

		var testo1=jQuery('#choose_a_simulation').val();
		//console.log('plot '+sec+" "+cou);
		var testo2=jQuery('#choose_a_sector option:selected').html();


		console.log('spider_select '+testo1+" "+testo2);
		jQuery.each( jQuery('#'+id+' tbody tr'), function(k,v){
			v = jQuery(v);


			//console.log(v.text().toLowerCase())	;
			//console.log(testo2.toLowerCase())	;

			if( v.text().toLowerCase().indexOf(testo1.toLowerCase()) > -1 &&  v.text().toLowerCase().indexOf(testo2.toLowerCase()) > -1)
				{
					v.show();
				}
			else
				{
					v.hide();
				}
		} );



	createSpiderChart(false);
	



}



if(typeof window.console == 'undefined') { window.console = {log: function (msg) {} }; }
