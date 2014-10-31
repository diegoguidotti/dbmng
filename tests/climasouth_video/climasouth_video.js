

jQuery(function(){

		console.log('a');
		
		var vh=jQuery('#intro video').height();

		var tm=((vh-400));
		console.log(vh+" "+tm);
		jQuery('#intro video').css('margin-top','-'+tm+'px');


});
