function init_mobile(){
	debug('start mobile');
  document.location.href='#login_page';
	

	var uid = jQuery.jStorage.get("dbmng_user");
	if(uid){
    createTableList(uid);
	}


	jQuery.mobile.pageContainer.on("pagechange", function(event, data) {
        var toPage = data.toPage[0].id;
				debug('change page '+toPage)
        if(toPage=='table_edit'){
						jQuery("#"+toPage).trigger('create');
            jQuery("#"+toPage+" ul").listview().listview('refresh');
        }
				else if(toPage=='record_edit'){ 
					//jQuery("#"+toPage+"").trigger('create');
				}
				else if(toPage=='loading'){ 
					
					//after taken the picture goes back to #record_edit
					if(jQuery.jStorage.get('tmp_picture')){
						document.location.href='#record_edit';
						jQuery.jStorage.deleteKey('tmp_picture')
					}
					else{
						document.location.href='#login_page';
					}
				}
	});

}


function dbReset(){

	jQuery.each(jQuery.jStorage.index(), function(k,v){
		jQuery.jStorage.deleteKey(v);
	});
	init_mobile;

}


function createTableList(d){
	debug('createTableList');

	jQuery('#login_form').hide();
  jQuery('#logout_form').show();
  jQuery('#login_message').html('Welcome '+d.user_name); 

	var h=' <ul class="table_container" data-role="listview" >';
	jQuery.each(d.table, function(k,v){
	  //h+='<div   data-role="collapsible" data-collapsed="true">';
	  h+='<li id="table_'+v.id_table+'"><a href="#" onClick="showTable('+v.id_table+')">'+v.table_label+'</a></li>';
	  //h+='</div>';
	});
	h+="</ul>";
	
	//jQuery('#table_list_container').html(h);
	jQuery("#table_list div:jqmData(role=content)").html(h);
  jQuery.mobile.changePage(jQuery("#table_list"));
}


function showTable(id_table) {
	//Check if exist an aForm in the jstorage
	jQuery.mobile.changePage("#table_edit");

	var default_call='ajax.php';
	if(base_call){
		default_call=base_call+"ajax.php";
	}

	var db= new Dbmng(id_table, {
			'div_element':'table_edit',   //div id containing the table
			'ajax_url':default_call,  //Where is locate the php with ajax function (relative to the current PHP file)
			'prepend': 1,    			  	//invert the order of records (new's one above)
			'auto_sync': 0,    			  //Save automatically to the server record by record
			'inline':0,               //Enable editing in the table without creating a new form
			'auto_edit':1,            //Run the synch after moving on a new row; auto edit is available only in auto_sync mode
			'mobile':1								//Enable jQuery-mobile css style
		});  
		db.start();	


}

function doLogin(){
  var user_id=jQuery('#user_id').val();
  var password=jQuery('#password').val();
	debug('doLogin');

  if(user_id!='' && password!=''){
    var call=base_call+'ajax_mobile.php?do_login=on&name='+user_id+'&pass='+password;
    
    jQuery.ajax({
      url: call,
      success: function(data) {
				debug(data);
        var d=eval(' ('+data+');');

        if(d.login){  
        	jQuery.jStorage.set("dbmng_user", d);
          createTableList(d);
        }
        else{                    
          showMessageBox(d.msg);                    
        }
          
      }
    });
  }
  else{
	  showMessageBox('Please insert userID and password');
	  
	  //alert(call);
  }
 
 return false;
}


function doLogout(){
	debug("doLogout");
  var call=base_call+'ajax_mobile.php?do_logout=on';
  jQuery.ajax({
    url: call,
    success: function(data) {
      var d=eval(' ('+data+');');
      //console.log(data);
      
      if(!d.login){                                     
				jQuery.jStorage.deleteKey("dbmng_user");
	
				jQuery("#login_message").html('');
	
	      jQuery('#login_form').show();
	      jQuery('#logout_form').hide();
      }
      else{                    
          ;
      }
    }
  });
     
 return false;
}

function goToLogin(){
	debug("goToLogin");
	jQuery.mobile.changePage("#login_page");
	//jQuery.mobile.changePage("#login_page");
}


// Create a dialog and display it
function showMessageBox (message) {
	
	//console.log(message);
	
	// Create it in memory
	var dlg = $("<div />")
	    .attr("data-role", "dialog")
	    .attr("id", "dialog");
	var content = $("<div />")
	    .attr("data-role", "content")
	    .append($("<span />").html(message));
	content.append("<a href=\"javascript:$('.ui-dialog').dialog('close'); " +
	    "return false;\" data-role=\"button\" data-rel=\"back\">Close</a>");
	
	dlg.append(content);
	
	dlg.appendTo($.mobile.pageContainer);
	
	// show the dialog programmatically
	jQuery.mobile.changePage(dlg, {role: "dialog"});
}



/*
 * Return true/false indicating whether we're running under Cordova/Phonegap
 */
function is_cordova() {
    return (typeof(cordova) !== 'undefined' || typeof(phonegap) !== 'undefined');
};
