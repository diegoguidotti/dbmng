function dbmng_module_search(id)
{
	jQuery('#dbmng_search').keyup(function(){
		jQuery.each( jQuery('#'+id+' tbody tr'), function(k,v){
			v = jQuery(v);
			testo = jQuery('#dbmng_search').val();
			if( v.text().toLowerCase().indexOf(testo) > -1 )
				{
					v.show();
				}
			else
				{
					v.hide();
				}
		} )
	})
}