function init_mobile(){
	console.log('start mobile');
}


function doLogin(){
    var user_id=jQuery('#user_id').val();
    var password=jQuery('#password').val();
    
    if(user_id!='' && password!=''){
               
        var call=base_call+'?do_login=on&name='+user_id+'&pass='+password;
        
        jQuery.ajax({
            url: call,
            success: function(data) {
								console.log(data);
                var d=eval(' ('+data+');');

                if(d.login){              
                    
                    jQuery('#login_form').hide();
                    jQuery('#logout_form').show();
                    jQuery('#login_message').html('Welcome '+user_id); 

										var h='<div class="table_container" data-role="collapsible-set">';
										jQuery.each(d.table, function(k,v){

											  h+='<div id="table_'+v.id_table+'"  data-role="collapsible" data-collapsed="true">';
                        h+='<h3>'+v.table_name+'</h3>';
												h+="Ciao sDKLSA JDKASLJ DKSALJ DKSAL JDKLSAJDKLSA JDKLS";
                        h+='</div>';
												
										});
										h+="</div>";

										jQuery('#table_list_container').html(h).trigger("create");;
										
										


                    jQuery.mobile.changePage("#table_list");
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
        var call=base_call+'?do_logout=on';
        jQuery.ajax({
            url: call,
            success: function(data) {
                var d=eval(' ('+data+');');
                //console.log(data);
                
                if(!d.login){                                     
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
