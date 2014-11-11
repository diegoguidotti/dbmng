
jQuery(function(){
		jQuery("#cs_short").prop('muted', true);

		resize();

		jQuery( window ).resize(function() {
			resize();
		});		

		

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

});


function resize(){
   console.log('a');
		
		var vh=jQuery('#intro video').height();

		//var tm=((vh)/2);

			var tm	=((vh-400));

		console.log('Video alto:'+vh+" margine: -"+tm);
		jQuery('#intro video').css('margin-top','-'+tm+'px');


		var left=jQuery('#about div.row').offset().left;
		jQuery('#intro #cs_video_title').css('left', left+'px');
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

if(typeof window.console == 'undefined') { window.console = {log: function (msg) {} }; }
