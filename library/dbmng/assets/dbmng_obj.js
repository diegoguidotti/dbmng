var DEBUG=true;
if(typeof window.console == 'undefined') { window.console = {log: function (msg) {} }; }

function debug(d){
	if(DEBUG){
		if('console' in self && 'log' in console){
			console.log(d);
		}else{
			;
			//alert(d);
		}
	}
	
	if(typeof window.console == 'undefined') { window.console = {log: function (msg) {} }; }
}

function t(txt){
	return txt;
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng
// ======================
/// General class to visualize and edit a single table
/**
\param f  		Associative array with all the characteristics of the table
\param p  		Associative array with some custom variable used by the renderer
*/
function Dbmng(idt , p) {
  this.id_table = idt;
 
	//check if an ajax call is running
	this.running=false;

	this.prog=0;
	
	//taken from http://stackoverflow.com/questions/4785724/queue-ajax-requests-using-jquery-queue
	this.am = (function() {
     var requests = [];
     return {
        addReq:  function(opt) {
	        requests.push(opt);
        },
        removeReq:  function(opt) {
            if( jQuery.inArray(opt, requests) > -1 )
	            requests.splice(jQuery.inArray(opt, requests), 1);
        },
        run: function() {
	        var self = this,
	          orgSuc;

            if( requests.length ) {
	            oriSuc = requests[0].complete;
	
	            requests[0].complete = function() {
								if( typeof oriSuc === 'function' ) oriSuc();
								requests.shift();
								self.run.apply(self, []);
	            };   
							debug('manda');
							debug(requests[0]);
	            jQuery.ajax(requests[0]);
            } else {
              self.tid = setTimeout(function() {
								self.run.apply(self, []);
              }, 1000);
            }
        },
        stop:  function() {
	        requests = [];
	        clearTimeout(this.tid);
        }
     };
	}());

	this.am.run(); 
  this.aData = {'records': new Array()};

	//setup parameters
  this.aParam = p;

	if(!this.aParam.ui){
		this.aParam.ui = Array();
	}


	if(!this.aParam.loading_message){
		this.aParam.loading_message = 'The table is being loaded...';
	}

	this.inline=0;
	if(p.inline){
		this.inline=p.inline;
	}	


	this.prepend=0;
	if(p.prepend){
		this.prepend=p.prepend;
	}	

	this.auto_sync=0;
	if(p.auto_sync){
		this.auto_sync=p.auto_sync;
	}	

	this.child=Array();

	this.auto_edit=0;
	//auto edit is available only for auto_sync
	if(p.auto_edit && this.auto_sync==1){
		this.auto_edit=p.auto_edit;
	}	

	this.mobile=0;
	if(p.mobile){
		this.mobile=p.mobile;
	}	

	this.ajax_url='dbmng_ajax.php';
	if(this.aParam.ajax_url){
		this.ajax_url=this.aParam.ajax_url;
	}

	this.id='dbmng_'+this.id_table;
	if(this.aParam.div_element){
		this.id+='_'+this.aParam.div_element;
	}

	debug('create_db '+this.id);
	var obj=this;

	var html="";
  html += "<div id='"+this.id+"_form'></div>\n";
  html += "<div id='"+this.id+"_view'>"+this.aParam.loading_message+"</div>\n";

	if(obj.mobile){
		jQuery('#'+this.aParam.div_element+" div:jqmData(role=content)").html(html);
	}
	else{	
		jQuery('#'+this.aParam.div_element).html(html);
	}


	//check when exit the page
	jQuery(window).bind('beforeunload', function(){ 
		if(!obj.isSaved()){
			var msg=t('Please save before exit');
			return (msg);

			obj.updateStorage();
		}
	});
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.start
// ======================
/// search for the data to the server and, if founded, create the table
/**
*/
Dbmng.prototype.start = function()
{
	var obj=this;

	var saved_form=null;
	var saved_data=null;
	if(!obj.auto_sync){
		saved_form=jQuery.jStorage.get(this.id+"_form");
		saved_data=jQuery.jStorage.get(this.id+"_data");
	}



	if(!obj.auto_sync && saved_form && saved_data){
		debug("LOAD FROM JSTORAGE");
		obj.aData =saved_data;
		obj.aForm =saved_form;
		obj.createTable();
	}
	else {
		debug("LOAD FROM AJAX");
		
		var form=obj.aForm;
		//form={"id_table":20,"table_name":"mm_test","table_label":"Questa tabella si chiama pippo","fields":{"nome":{}}};

		var input_data=		{"id_table" : obj.id_table, "get_records": true };	
		if(obj.aParam.id_parent){
			input_data.id_parent=	obj.aParam.id_parent;
		}
		if(obj.aParam.fk){
			input_data.fk=	obj.aParam.fk;
		}
		

		this.am.addReq({ //jQuery.ajax({
			url: this.ajax_url,
			type: "POST",
			data: input_data,
			dataType: "json",
			error: function (e) {
				debug('Error request');
				debug(e);
				debug(e.responseText);
			},
			success: function (data) {
				debug('send request success');
				debug(data);	
				if(data.records){
					var newRecords={};
					jQuery.each(data.records,function(k,v){
						newRecords[Guid.newGuid()]={'state':'ok', 'record':v};
					});
					obj.aData = {'records': newRecords};
					obj.aForm = data.aForm;

					if(!obj.auto_sync){
						//save record and aForm in jStorage
						jQuery.jStorage.set(obj.id+"_form", obj.aForm );
						jQuery.jStorage.set(obj.id+"_data", obj.aData);
					}
					
					obj.createTable();
				}
				else{
					html='no records returned';
					if(data.msg)
						html=data.msg;

					if(data.error){
						html='An error occurred: '+data.error;
					}
						
					jQuery('#'+obj.aParam.div_element).append('<div class="dbmng_err">'+html+'</div>');
					debug(data);
				}
			}
		});
	}
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.createTable
// ======================
/// Populate the table using aData
/**
*/
Dbmng.prototype.createTable = function(){	

	debug("createTable - start");

	//show the table and hide the form
	jQuery("#"+this.id+"_view").show();	
	jQuery("#"+this.id+"_form").hide();	

	//store the object to refer to it in the subfunction
	var obj=this;

	//Create the two container for the table and the form
	var html='';
	if(obj.aForm.table_label){
		if( obj.mobile == 1 ){
			jQuery('#table_edit_header').html(obj.aForm.table_label);
			//html += "<div data-role='header'><h1>" + obj.aForm.table_name + "</h1></div>\n";
		}
		else{
			html += "<div id='dbmng_table_header'><h1 class='dbmng_table_label'>" + obj.aForm.table_label + "</h1></div>\n";
		}	
	}

	if( obj.mobile == 1 ){
		html+="<ul class='dbmng_list_view' id='"+this.id+"_table' data-role='listview' data-filter='true' >";
		html+="</ul>";
	}
	else { 
		html += "<table class='dbmng_table table' id='"+this.id+"_table'>\n";
		//Add header
		html += "<thead>\n";
		html += "<tr >\n";
		jQuery.each(obj.aForm.fields, function(index, field){ 
			var f = field;
			if( layout_view_field_table(f.skip_in_tbl) ){
				if(!dbmng_check_is_pk(f)){
					html += "<th class='dbmng_field_$fld'>" + t(f.label) + "</th>\n";
				}
			}
		});
		html += "<th class='dbmng_functions'>" + t('actions') + "</th>\n";
		html += "</tr>\n";
		html += "</thead>\n";
		html += "<tbody></tbody></table>";	
	}

	jQuery('#'+obj.id+'_view').html(html);
  

	console.log(this.aData.records);
	//Create the body of the table
	jQuery.each(this.aData.records, function(k,value){
		var o = value.record;
		var state = value.state;
		var id_record=k;

		//check if the field has a valid object			
		if(typeof o != 'undefined'){
			if( obj.mobile == 1 ){
				var html_row = "<li ><a href='#' class='dbmng_edit_button'  ><div id='"+obj.id+"_"+k+"' class='"+state+"'>";
				html_row += obj.createRow(value, id_record);	//<a href = "#">record name</a>
				html_row += "</div></a></li>\n";
			
				id_element='#'+obj.id+'_view ul';

			}
			else{
				var html_row = "<tr id='"+obj.id+"_"+k+"' class='"+state+"'>";
	
				html_row += obj.createRow(value, id_record);
				html_row += "</tr>\n";	

				id_element= '#'+obj.id+'_view tbody';
			}

				//Save the table row in DOM
				if(obj.prepend){
					jQuery(id_element).prepend(html_row);
				}
				else{
					jQuery(id_element).append(html_row);
				}

			//attach command assign the click function to delete/update/restore/insert button			
			obj.attachCommand(id_record);
		}
	});

	var id_sel_add    = ''+obj.id+'_add';
	var id_sel_save   = ''+obj.id+'_save';
	var id_sel_reload = ''+obj.id+'_reload';
	if( obj.mobile == 1 ){
		id_sel_add 	  = 'record_add';
		id_sel_save   = 'record_save';
		id_sel_reload = 'record_reset';
	}
	

	var insert_label="Insert";

	if(obj.aParam.ui.btn_lst_name){
		insert_label=obj.aParam.ui.btn_lst_name;
	}

	//Add the insert button
	if( obj.mobile != 1 )
		jQuery('#'+obj.id+'_view').append("<a data-inline='true' data-role='button' id='"+id_sel_add+"'>"+t(insert_label)+"</a>");
	
	
	jQuery("#"+id_sel_add).unbind().click(function(){
		var current=jQuery('#'+obj.id+"_table tr.working");

    if(current.length>0){ //if exist an editing record save it
        current.removeClass('working')
				var id_record=	current.attr('id').substring(obj.id.length+1,current.attr('id').length);
				if(current.hasClass('auto_edit_insert')){
					obj.prepareInsert(id_record);
				}
				else{
					obj.prepareUpdate(id_record);
				}
    } 
		obj.createForm();
	});


	if(!obj.auto_sync){
		if( obj.mobile != 1 )
			jQuery('#'+obj.id+'_view').append(" - <a data-inline='true'data-role='button' id='"+id_sel_save+"'>"+t("Save")+"</a>");

		jQuery("#"+id_sel_save).unbind().click(function(){
			obj.syncData();		
		});	
	}

	if( obj.mobile != 1 &&  obj.auto_sync != 1 )
		jQuery('#'+obj.id+'_view').append(" - <a data-inline='true' data-role='button' id='"+id_sel_reload+"'>"+t("Reset")+"</a>");

	jQuery("#"+id_sel_reload).unbind().click(function(){
		if(obj.isSaved()){
			obj.resetDb();
		}
		else{
			var r=confirm(t('Do you confirm? You will lose all the local changes.'));
			if (r==true){
				obj.resetDb();	
			}
		}
	});

	if(this.auto_edit && this.inline){
		jQuery("#"+obj.id+"_table tr").on('click', function (e) {  
	    var el=jQuery(this);        
	
	    if(el.hasClass('working')){
				; //keep working...
	    }
	    else{             
	      var current=jQuery('#'+obj.id+"_table tr.working");
	      //debug(current);
	      //debug(current.length);
	
	      if(current.length>0){ //if exist an editing record save it
	        debug('save the record '+current.attr('id'));   
	        current.removeClass('working')
	
					var id_record=	current.attr('id').substring(obj.id.length+1,current.attr('id').length);
	
					if(current.hasClass('auto_edit_insert')){
						obj.prepareInsert(id_record);
					}
					else{
						obj.prepareUpdate(id_record);
					}
	      } 
				else{
					var id_record=	el.attr('id').substring(obj.id.length+1,el.attr('id').length);
					obj.createForm(id_record);	
				}               
	      el.addClass('working');
	      debug('Start editing record '+this.id);
	    }        
		}); 
	}


	if(obj.mobile){
		try{
			jQuery('#table_edit').trigger("create");
			jQuery('#'+this.id+'_table').listview().listview('refresh');
			debug('ok refresh');
		}
		catch(e){debug(e);
			debug('err refresh');
			
		}
	}


	debug("createTable - end");

	if(obj.aParam.jsHook){
		if(obj.aParam.jsHook.create_table_end){
			executeFunctionByName(obj.aParam.jsHook.create_table_end, window );		
		}		
	}
	console.log(obj.aParam);
	//return html;
};

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.resetDb
// ======================
/// Populate the table using aData
/**
*/
Dbmng.prototype.clearJStorage = function() {
	var obj=this;
	if(!obj.auto_sync){
		jQuery.jStorage.deleteKey(obj.id+"_data");
		jQuery.jStorage.deleteKey(obj.id+"_form");
	}
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.resetDb
// ======================
/// Populate the table using aData
/**
*/
Dbmng.prototype.resetDb = function() {
	var obj=this;
	obj.clearJStorage();
	obj.start();		
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.syncData
// ======================
/// Populate the table using aData
/**
*/
Dbmng.prototype.syncData = function() {	

	var obj=this;
	console.log("SYNCDATA "+obj.id);

	

	var q=jQuery(document).queue('myAjaxQueue'+obj.id, function() {
		obj.running=true;

		console.log("SYNCDATASTART "+obj.id);

		if(obj.mobile && obj.isSaved()){
			debug('NO DATA TO SAVE '+obj.id);
		
			if(obj.aParam.mobile){
				showMessageBox(t("There are no data to save"));
			}

			obj.running=false;
		}
		else {

			console.log('start '+obj.prog);

			if(obj.aParam.mobile){
				dialogStart(t("Starting to upload data & images"));
			}

			obj.prog = obj.prog+1;
			

			//TODO: check if it is better to use ajaxmanager
			//obj.am.addReq({ //
			jQuery.ajax({
				url: obj.ajax_url,
				type: "POST",
				data: {"id_table" : obj.id_table, "inserted":  JSON.stringify(obj.aData.inserted), "deleted": JSON.stringify(obj.aData.deleted) , "updated": JSON.stringify(obj.aData.updated) }, 
				dataType: "json",
				success: function (data) {


					console.log("AJAXSTART "+obj.id);

					var error="";

					

					if(data.deleted){
						//dialogAppend('record to delete: '+data.deleted.length);
						jQuery.each(data.deleted, function(k,v){
							if(v.ok==1){
								delete obj.aData.records[k];
								delete obj.aData.deleted[k];
								dialogAppend(t('Record deleted'), obj.aParam.mobile);							
							}
							else{
								obj.aData.records[k].error=v.error;
								error++;
								dialogAppend(t('Delete Record Error'), obj.aParam.mobile);	
							}
						});
					}

					var pk_key=obj.aForm.primary_key[0];
			
					

					//check if exist a picture field
					var fld_picture= new Array();
					var img_to_upload= new Array();
					jQuery.each(obj.aForm.fields, function(k,v){
						if(v.widget=='picture'){
							fld_picture.push(k);
							console.log ('add picture field '+k);
						}
					});


					if(data.inserted){
						
						jQuery.each(data.inserted, function(k,v){
							if(v.ok==1){
								obj.aData.records[k].state='ok';
								obj.aData.records[k].record[pk_key] = v.inserted_id;
								delete obj.aData.inserted[k];	
								obj.aData.records[k].error='';	
								dialogAppend(t('Record inserted'),obj.aParam.mobile);												

							}
							else{														
									obj.aData.records[k].error=v.error;
									dialogAppend(y('Insert Record Error'),obj.aParam.mobile);	
									error++;
							}
						});
					}


					
					if(data.updated){						
						jQuery.each(data.updated, function(k,v){
							if(v.ok==1){
								if(obj.aData.records[k]){
									obj.aData.records[k].state='ok';	
									obj.aData.records[k].error='';						
									delete obj.aData.updated[k];	
									dialogAppend(t('Record updated'),obj.aParam.mobile);		

								}
								else{
									alert('Record '+k+' not found in updated');
								}
							}
							else{		
								obj.aData.records[k].error=v.error;
								error++;
								dialogAppend(t('Update Record Error'),obj.aParam.mobile);
							}
						});
					}

					if(error==0){
						dialogAppend(t("All the data has been uploaded correctly"),obj.aParam.mobile);
					}
					else{
						dialogAppend(t("Some error occurred during data uploading. Records affected: ")+error,obj.aParam.mobile);

					}


					if(is_cordova()){
						//if exist a picture field find some record to be uploaded

						

						jQuery.each(fld_picture, function(k2,fld_name){										
							jQuery.each(obj.aData.records, function(k,rec){	
										try{
											if(rec){		

												console.log("fldpict"+rec.record[fld_name]);
												var img = JSON.parse(rec.record[fld_name]);
												if(img){
													if(!img.uploaded){
															var o={'imageURI': img.imageURI, rec: obj.aData.records[k], 'fld_name':fld_name, 'gui':k};
															img_to_upload.push(o);												
													}
												}
											}
										}
										catch(e){console.log(e);}
								});
						});

						obj.current_image=0;

						if(	img_to_upload.length>0){
							dialogAppend(t("Start pictures uploading")+" ("+img_to_upload.length+") ",obj.aParam.mobile);
						}
						else{
								dialogAppend(t("There are no picture to be uploaded"),obj.aParam.mobile);
								dialogClose();
						}		

						jQuery.each(img_to_upload, function(k,v){
							
							obj.uploadImage(v);
						});
					}
					else{
						dialogClose();
					}



					obj.createTable();
					if(!obj.auto_sync){	
						obj.updateStorage();
					}
					

					//debug('end '+obj.prog);
					obj.running=false;

					console.log("AJAXEND "+obj.id);

					//end of Success	
				},
				error: function (xhr, ajaxOptions, thrownError){

					dialogAppend(t("The server replied with an error")+": "+thrownError,obj.aParam.mobile);
					dialogClose();
			  	console.log(xhr);
					console.log(ajaxOptions);
					console.log(thrownError);
					obj.running=false;
		  	 //debug(thrownError);
			  }   
			});	//end of Ajax

					console.log("SYNCDATAEND "+obj.id);

		}
	}); //end of queue

	if(!obj.running){
	  jQuery(document).dequeue('myAjaxQueue'+obj.id); 
  }
}


Dbmng.prototype.uploadImage = function (v) {
	var obj=this;
	debug('Try to upload image '+JSON.stringify(v));

	fileURL=v.imageURI;



//  var uri = encodeURI(base_call+'ajax_mobile.php');
  
	var uri = encodeURI(this.ajax_url);
debug("URI: "+uri);

	var options = new FileUploadOptions();
	options.fileKey="file";
	options.fileName=fileURL.substr(fileURL.lastIndexOf('/')+1);
	options.mimeType="text/plain";
	options.chunkedMode = false;
	options.headers = {
		  Connection: "close"
	};


		var params = new Object();
    params.upload_picture = "ok";
    params.json_picture = JSON.stringify(v);
    params.id_table = obj.id_table; 
		params.fld_name = v.fld_name;  
		params.gui = v.gui;    

    options.params = params;
	//var headers={'upload_picture':'ok'};

	

	var ft = new FileTransfer();
	obj.current_image++;
	var ci=obj.current_image;

	dialogAppend(t('Uploading image')+' '+ci+': <span id="upload_prog_'+ci+'"></span> ',obj.aParam.mobile);	

	ft.onprogress = function(progressEvent) {
		//console.log("AAAAAAAAAAAAAAAAAAAAAAA "+progressEvent.loaded +" "+ progressEvent.total);

		jQuery("#upload_prog_"+ci).html(""+(Math.round(100*(progressEvent.loaded / progressEvent.total)))+"%");

			
	};
	
	ft.upload(fileURL, uri, 

		function (r) {
				console.log("Code = " + r.responseCode);
				console.log("Response = " + r.response);
				console.log("Sent = " + r.bytesSent);

				try{
					var d=eval(' ('+r.response+');');

					console.log("XXX "+d.ok+"  "+d.id_table+" "+d.pk+" "+d.fld_name+" "+d.gui);

					if(d.ok==1){
						
		
						
			
						obj.uploadedImage(d.gui, d.fld_name, ci, d.img_uri);
						;

					}
					else if(d.ok==0){
						alert(""+d.error);
					}
					else{
				
					}
				}
				catch (e){
					alert(e+" "+r.response);
				}
		}	

		, function (error) {
				if(error.code==3){
                //timeout (it may happen for ios)
                dialogAppend(t('Fixing image upload')+' ',obj.aParam.mobile);
                obj.uploadedImage(v.gui, v.fld_name, ci, v.img_uri);          
          }
          else{
						dialogAppend(t('Error during image uploading')+' '+error.code,obj.aParam.mobile);
						dialogClose();
						//alert("An error has occurred: Code = " + error.code);
						console.log("upload error source " + error.source);
						console.log("upload error target " + error.target);
          }
		}
		, options);

}


Dbmng.prototype.uploadedImage = function (gui, fld_name, ci, img_uri) {
	var obj=this;

	
	
	if(obj.aData.records[gui]){
		var img =JSON.parse(obj.aData.records[gui].record[fld_name]);
		img.uploaded=1;
		if(img_uri){
			if(img_uri!=''){
				img.imageURI=img_uri;
			}
		}

		obj.aData.records[gui].record[fld_name]=JSON.stringify(img);
		obj.updateStorage();

		jQuery("#upload_prog_"+ci).html(t("completed"))
		dialogClose();
		
	}
	else{
		console.log("XXX Not found "+gui+" "+fld_name+" "+obj.id);
		dialogAppend(t('Image uploaded'),obj.aParam.mobile);	

		
		
	}


}




/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.attachCommand
// ======================
/// this function add the available function for each table row
/**
*/
Dbmng.prototype.attachCommand = function (id_record) {
	var obj=this;

	jQuery('#'+obj.id+'_del_'+id_record).unbind().click(function(e){		
		if(!obj.auto_sync){
			obj.deleteRecord(id_record);
		}
		else{
			var r=confirm(t('Do you confirm?'));
			if (r==true){
				obj.deleteRecord(id_record); 
			}			
		}
		e.stopPropagation(); //stopPropagation block the auto_edit features when clicking on table row
	});

	jQuery('#'+obj.id+'_restore_'+id_record).unbind().click(function(e){						
		obj.restoreRecord(id_record);
		e.stopPropagation();
	});

	jQuery('#'+obj.id+'_upd_'+id_record).unbind().click(function(e){	
			obj.createForm(id_record);		
					
		//if(!obj.auto_edit){
		//}
	});
		
	jQuery('#'+obj.id+'_dup_'+id_record).unbind().click(function(e){						
		obj.duplicateRecord(id_record);
		e.stopPropagation();
	});


	if( obj.mobile == 1 ) {
		//the click event has to be associated to the parent of the div containing the record
		//in that way will be triggered if you click in any way of the listview item
		
		jQuery('#'+obj.id+'_'+id_record).parent().unbind().click(function(e){		
			obj.createForm(id_record);		
		});


		/*
		var tapTime = 0;						
		jQuery('#'+obj.id+'_'+id_record).parent().unbind().on('vmousedown vmouseup', function(e){
	    if( e.type == 'vmousedown' ) {
	    	tapTime = new Date().getTime();
	    }
	    else{
	    	var duration = (new Date().getTime() - tapTime);
	    	if( duration >1000 ){
	    		//taphold
		      debug("dbmng Taphold show the content menu");
		      
		      var state = obj.aData.records[id_record].state
					if( state == 'del' ){
						jQuery('#tapholdmenu_res').show();
						jQuery('#tapholdmenu_del').hide();
					}
					else{
						jQuery('#tapholdmenu_res').hide();
						jQuery('#tapholdmenu_del').show();
					}
		      jQuery('#tapholdmenu').popup("open");
		
					jQuery('#tapholdmenu_del').unbind().on('click', function (event) {
							obj.deleteRecord(id_record);
					    //jQuery('.androidMenu').toggle();
					});		
					jQuery('#tapholdmenu_dup').unbind().on('click', function (event) {
							obj.duplicateRecord(id_record);
					    //jQuery('.androidMenu').toggle();
					});		
					jQuery('#tapholdmenu_res').unbind().on('click', function (event) {
							obj.restoreRecord(id_record);
					    //jQuery('.androidMenu').toggle();
					});		
	    	}
	    	else{
	        debug("dbmng Tap");
					obj.createForm(id_record);			
	    	}
	  	}	  	
		});
		*/
		
	}
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.isSaved
// ======================
/// check if there are data not synched with the db
/**
*/
Dbmng.prototype.isSaved = function() {
	var obj=this;
	var il=0;
	if(obj.aData.inserted)
		il=Object.keys(obj.aData.inserted).length ;

	var dl=0;
	if(obj.aData.deleted)
		dl=Object.keys(obj.aData.deleted).length ;

	var ul=0;
	if(obj.aData.updated)
		ul=Object.keys(obj.aData.updated).length ;

	debug(obj.id+" ins:"+il+" de: "+dl+" up: "+ul);

	if(il==0 && dl==0 && ul==0){
		return true;
	}
	else{
		return false;
	}
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.createRow
// ======================
/// this function create a table row
/**
*/
Dbmng.prototype.createRow = function (value, id_record) {
	var obj=this;
	var state=value.state;
	var o=value.record;		


	//console.log("Add row ");
	//console.log(value);

	var added_field_mobile=0;
	//debug(value.record);

	var html_row="";
	var html_img="";
	
	//console.log(obj.aForm.fields);
	//console.log(o);
	if(typeof o != 'undefined') {
		for( var key in obj.aForm.fields )	{        
			//get the field parameters
      var f = obj.aForm.fields[key];
      if(f){
				if( layout_view_field_table(f.skip_in_tbl) ){
					
					if ( o.hasOwnProperty(key)){
						var field_value=o[key];

						if(!dbmng_check_is_pk(f)){

							var html_value='';


							if(typeof window["dbmng_"+f.widget+"_html"] == "undefined") {
								html_value = dbmng_widget_html(field_value, f ); 
							}
							else{
								html_value =  executeFunctionByName("dbmng_"+f.widget+"_html", window, field_value, f );
							}

							if(obj.mobile){	
								if( f.widget == "picture" ){
									//debug("picture: "+html_row);
									html_row = html_value+html_row;
								}
								else if(added_field_mobile<2){
									//debug("added_field_mobile+<2: "+html_row);
									html_row += html_value +" ";
									added_field_mobile++;
								}
								else if(added_field_mobile==2){
									//debug("added_field_mobile+=2: "+html_row);
									html_row = "<h3>"+html_row+"</h3>";
									html_row += "<p>"+html_value+"</p>";
									added_field_mobile++;
								}
							}	
							else{
								if(html_value=='')
									html_value='&nbsp;';
								html_row += "<td>" + html_value + "</td>";
							}
						}
					}
					else{
						html_row += "<td>aaaa-</td>";
					}				
				}
			}
			else{
				//debug('field '+key+' not found in aForm' );
			}
		}
	}
	
	// available functionalities
	if( obj.mobile == 1 ){
		
		if(html_row ==''){
			//html_row+='No data available aaaa';
			debug('No data available');
			debug(value);
		}
		else{

			html_row = '<span id="'+obj.id+'_edit_'+id_record+'">' +  html_row + "</span>"; 
			if(value.error && value.error!=''){						
						html_row += '<span title="'+value.error+'" class="dbmng_error">'+t('Error')+': '+value.error+'</span>';
			}
		}

	}
	else{
		html_row += "<td class='dbmng_functions'>";

		var nDel=1; nUpd=1; nDup=0;

		if(obj.aParam['user_function']){
			console.log('ii');
			nUpd = ((typeof  obj.aParam['user_function']['upd'] != 'undefined') ? obj.aParam['user_function']['upd'] : 1);
			nDel = ((typeof  obj.aParam['user_function']['del'] != 'undefined') ? obj.aParam['user_function']['del'] : 1);
			nDup = ((typeof  obj.aParam['user_function']['dup'] != 'undefined') ? obj.aParam['user_function']['dup'] : 0);
		}



		if(state=='del'){
				html_row += '<span id="'+obj.id+'_restore_'+id_record+'"><a  class="dbmng_restore_button"  >' + t('Restore') +'</a>' + "&nbsp;</span>";
		}
		else {
						
				if( nDel == 1 ){				
					html_row += '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button" >' + t('Delete') +'</a>' + "&nbsp;</span>";
				}
				if( nUpd == 1 ){				
					html_row += '<span id="'+obj.id+'_upd_'+id_record+'"><a  class="dbmng_update_button" >' + t('Update') +'</a>' + "&nbsp;</span>";
				}
				if( nDup == 1 ){				
					html_row += '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";
				}
			
		} 

		if(value.error){
			console.log(value);
			html_row += '<span title="'+value.error+'" class="dbmng_error">'+t('Error!')+'</span>';
		}
		html_row += "</td>\n";
	}

	return html_row;
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.deleteRecord
// ======================
/// The function delete one record
/**
*/
Dbmng.prototype.deleteRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	debug("Find "+id_record+"to delete");

	var to_delete = obj.aData.records[id_record];	
	debug(to_delete);

	if(to_delete.state=='ins'){
		delete obj.aData.records[id_record];
		delete obj.aData.inserted[id_record];

		if(obj.mobile){
			jQuery('#'+obj.id+"_"+id_record).parents('li').first().html('');
		}
		else{
			jQuery('#'+obj.id+"_"+id_record).html('');
		}
	}
	else{
		if(to_delete.state=='upd'){
			delete obj.aData.updated[id_record];
		}

		to_delete.state = 'del';
		jQuery('#'+obj.id+"_"+id_record).removeClass( "ok" ).addClass( "del" );

		if(to_delete){
		 	if(!obj.aData.deleted){
				obj.aData.deleted={};
			}
			obj.aData.deleted[id_record]=(to_delete);
			debug('delete record '+id_record);
		}
		else{
			alert('Error. record to delete not found');
		}		
		jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(to_delete, id_record));
		//You need to attach again the restore button
		obj.attachCommand(id_record);
	}
	
	obj.goBackToTable();
	obj.updateStorage();
}				

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.restoreRecord
// ======================
/// The function restore a record
/**
*/
Dbmng.prototype.restoreRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	debug('restore');

	var to_restore = obj.aData.records[id_record];	
	to_restore.state = 'ok';

	debug(to_restore);
	delete obj.aData.deleted[id_record];		
	jQuery('#'+obj.id+"_"+id_record).removeClass( "del" ).addClass( "ok" );	
	jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(to_restore, id_record));
	//You need to attach again the restore button
	obj.attachCommand(id_record);

	obj.updateStorage();
}			

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.duplicateRecord
// ======================
/// The function duplicate a record
/**
*/
Dbmng.prototype.duplicateRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	
	var new_id_record=[Guid.newGuid()];
	var to_duplicate=jQuery.extend(true, {}, obj.aData.records[id_record]);
	to_duplicate.state = "ins";

	if(to_duplicate){
	 	if(!obj.aData.inserted){
			obj.aData.inserted={};
		}
		obj.aData.inserted[new_id_record]=to_duplicate;
		obj.aData.records[new_id_record]=to_duplicate;

		//add a Row
		if( obj.mobile == 1 ){
			var html_row = "<li ><a class='dbmng_edit_button' href='#record_edit' ><div id='"+obj.id+"_"+new_id_record+"' class='ins'>";
			html_row += "</div></a></li>\n";

			jQuery('#'+obj.id+'_view ul').prepend(html_row);

			obj.goBackToTable();

		}
		else{
			jQuery("#"+obj.id+"_table").append("<tr id='"+obj.id+"_"+new_id_record+"' class='ins'></tr>");
		}

		jQuery('#'+obj.id+"_"+new_id_record).html(obj.createRow(to_duplicate, new_id_record));	
		obj.attachCommand(new_id_record);
	}

	obj.updateStorage();
}				


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.insertRecord
// ======================
/// The function insert a new record
/**
*/
Dbmng.prototype.insertRecord = function(item, temporary_id_record) {
	//TODO deal with multiple key
	var obj=this;
	var id_record=[Guid.newGuid()];

	if(item){
	 	if(!obj.aData.inserted){
			obj.aData.inserted={};
		}
		obj.aData.inserted[id_record]=(item);
		obj.aData.records[id_record]=(item);

		debug('add record');

		//go back to table
		obj.goBackToTable();

		//Change the id to the temporary one
    jQuery('#'+obj.id+"_"+temporary_id_record).attr("id",obj.id+"_"+id_record);

		debug(jQuery('#'+obj.id+"_"+id_record));
		jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(item, id_record));	

		debug('create row');
		jQuery('#'+obj.id+"_"+id_record).removeClass( "ok" ).addClass( "ins" );	
		//You need to attach again the restore button
		obj.attachCommand(id_record);
	}
	else{
		alert('Error. record to insert undefined');
	}		
	obj.updateStorage();
}			

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.updateRecord
// ======================
/// The function update a selected record
/**
*/
Dbmng.prototype.updateRecord = function(item, id_record) {
	//TODO deal with multiple key
	var obj=this;
		
	if(item){
	 	if(!obj.aData.updated){
			obj.aData.updated={};
		}

		debug(item);
		if(item.state=='ins'){
			debug('do insert');
			obj.aData.inserted[id_record]=(item);
		}
		else{
			debug('do update');
			obj.aData.updated[id_record]=(item);	
		}
		
		obj.goBackToTable();
		
		if(!jQuery('#'+obj.id+"_"+id_record).hasClass("ins")){
			jQuery('#'+obj.id+"_"+id_record).removeClass( "ok" ).addClass( "upd" );
		}

		jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(item, id_record));
		//You need to attach again the restore button
		obj.attachCommand(id_record);
	}
	else{
		alert('Error. record to insert undefined');
	}		
	obj.updateStorage();
}			


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.goBackToTable
// ======================
/// The function after insert or update goes back to table page
/**
*/
Dbmng.prototype.goBackToTable = function() {
	var obj=this;

	if( obj.mobile == 1 ){
		  jQuery.mobile.changePage("#table_edit");
		  
			jQuery("#"+obj.id+"_view").trigger('updatelayout');	
			jQuery("#"+obj.id+"_view").show();	
		}
		else if(!this.inline){
			jQuery("#"+obj.id+"_view").show();	
			jQuery("#"+obj.id+"_form").hide();
		}	
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.updateStorage
// ======================
/// The function update jStorage
/**
*/
Dbmng.prototype.updateStorage = function() {
	var obj=this;

	if(obj.auto_sync){		
		obj.syncData();
	}
	else{
		debug('upd storage on '+obj.id+' records: '+obj.aData.records.length);
		jQuery.jStorage.set(obj.id+"_data", obj.aData);
	}
	//After update show the main table in not inline
}

	
/////////////////////////////////////////////////////////////////////////////
// Dbmng.layout_get_label
// ======================
/// The function get the label from metadb
/**
*/
Dbmng.layout_get_label = function(field_name, field, act){
	lb = field.label;

	if( typeof field.label_long != 'undefined' )
		lb = field.label_long;
	
	sRequired = "";
	if(typeof field.nullable != 'undefined' && field.nullable == 0 )
		sRequired = "<span class='dbmng_required'>*</span>";

	/*	
	if( typeof act != 'undefined' )	{
		if( act != "search" && act != "do_search" )	{
			if(typeof field.nullable != 'undefined' && field.nullable == 0 )
				sRequired = "<span class='dbmng_required'>*</span>";
		}
	}
	*/

	var cl="";
	//hiding label in jqueryMobile
	//if(obj.mobile==1){
	//	cl=' class="ui-hidden-accessible" ';
	//}

	return "<label "+cl+" for='"+field_name+"'>" + t(lb) + " " + sRequired + "</label>";
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.createForm
// ======================
/// The function create the form
/**
*/
//TODO: review create Form
Dbmng.prototype.createForm = function(id_record) {



	var obj=this;

	debug('createForm table |'+obj.id+'| id_record '+id_record);

	var act = 'ins';
	var item;

	if(typeof id_record!='undefined'){
		act='upd';
		item=obj.aData.records[id_record];
	}		
	
	debug('create_form'+obj.inline+" "+act);
	
	if(obj.inline==0){

		//in mobile app we do not need to hide the _view element otherwise the back button doesn't work
		if(obj.mobile==0){
			//hide the table and show the form
			jQuery("#"+obj.id+"_view").hide();	
			jQuery("#"+obj.id+"_form").show();	
		}
	}
	else{
		;
	}
	
	if(act=='ins'){
		id_record='tmp_'+Guid.newGuid();
		
		var cl="";
		if(obj.auto_edit && obj.inline){
			cl='class="working auto_edit_insert"';
		}

		if( obj.mobile == 1 ){
			var html_row = "<li ><a class='dbmng_edit_button' href='#record_edit' ><div id='"+obj.id+"_"+id_record+"' >";
			html_row += "</div></a></li>\n";

			jQuery('#'+obj.id+'_view ul').prepend(html_row);

		}
		else{
			jQuery("#"+obj.id+"_table").append("<tr "+cl+" id='"+obj.id+"_"+id_record+"'></tr>");
		}
	}
	
	var form='<form action="" method="POST">';
	var form='';

//	console.log(this.aForm);
//console.log(this.aForm.fields);

	jQuery.each(this.aForm.fields, function(index, field){ 			
		//debug(index + ": " + dbmng_check_is_pk(field));
		value = '';

		if(obj.mobile==1)
			form+='<fieldset data-role="controlgroup">';
		
		var view_field=true;
		if(dbmng_check_is_pk(field)){
			view_field=false;
		}
		else{
			if(!layout_view_field_table(field.skip_in_tbl) && obj.inline==1)
				view_field=false;
		}


		console.log("add field "+view_field +" "+dbmng_check_is_pk(field)+" "+index);
	
		if( view_field ){



		//if( !dbmng_check_is_pk(field) )
			if(obj.inline==1){
				form+='<td>';
			}
			else{
				form += Dbmng.layout_get_label(index, field, act);
			}

			var value='';

			if(act=='upd'){
				if(item.record){
					if(item.record[index]){
						value=item.record[index];							
					}
				}
			}
			form += obj.layout_form_widget(index, field, id_record, value, '', act) + "<br></br>";

			
			if(obj.inline==1){
				form+="</td>";
			}
		}
		else if (dbmng_check_is_pk(field))	{
			if(obj.inline==1){
				if(typeof field.skip_in_tbl != 'undefined' ){
					if(!layout_view_field_table(field.skip_in_tbl)){
						//form+="<td>&nbsp"+field.skip_in_tbl+"</td>";
					}
				}
			}
		}

		if(obj.mobile==1)
			form+="</fieldset>";
	});

	form+="</form>";
	form+="";


	
	
	datarole = "";
	datatheme = "";
	if( obj.mobile == 1 ){
		datarole  = "data-role='button'";
		datatheme = "data-theme='b'";
	}

	if(obj.inline==1){
		form+='<td>';
	}
	
	
	if(act=='ins'){
		form+="<a id='"+this.id+"_"+id_record+"_insert' "+datarole+" "+datatheme+">"+t("Insert")+"</a>";		
	}	
	else{
		var deleted=false;
		if(item){
			if(item.state=='del'){
				deleted=true;
			}
		}

		//console.log("XXXXX"+item.state);
		//console.log(item);
		if(deleted){
			form+="<a id='"+this.id+"_"+id_record+"_restore' "+datarole+" "+datatheme+">"+t("Restore")+"</a>";
		}
		else{	
			if(obj.mobile == 1 ){
				form+="<a class='dbmng_delete_button' id='"+this.id+"_"+id_record+"_delete' "+datarole+" "+datatheme+">"+t("Delete")+"</a>";		
				form+="<a class='dbmng_update_button' id='"+this.id+"_"+id_record+"_update' "+datarole+" "+datatheme+">"+t("Update")+"</a>";		
			}
			else{
				form+="<a class='dbmng_update_button' id='"+this.id+"_"+id_record+"_update' "+datarole+" "+datatheme+">"+t("Save")+"</a>";		
			}
		}

		if(obj.inline==1){
			form+='</td>';
		}
	}

	
	if( obj.mobile == 1 ){
		//html_del = '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button"  >' + t('Delete') +'</a>' + "&nbsp;</span>";
		//html_dup = '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";
		//jQuery('#record_edit_dup').html(html_dup); //.trigger("create");
		//jQuery('#record_edit_del').html(html_del);//.trigger("create");


		jQuery('#record_edit_del').unbind().click(function(e){
			debug('delete '+id_record);
			obj.deleteRecord(id_record);			
		});

		jQuery('#record_edit_dup').unbind().click(function(e){						
			obj.duplicateRecord(id_record);
			e.stopPropagation();
		});
			

	  jQuery.mobile.changePage("#record_edit");
		//jQuery('#record_edit_container').html(form).trigger("create");
		jQuery("#record_edit div:jqmData(role=content)").html(form).trigger("create");


		//TODO: temporary adHoc solution: proper filter should be defined
		if(typeof current_mon_point != 'undefined' ){
			if(current_mon_point){
				console.log("refresh mon point"+current_mon_point);
				if(jQuery('select[name=id_mon_point]').val()==""){
					console.log("DR");
					jQuery('select[name=id_mon_point]').val(current_mon_point).selectmenu('refresh', true);
				}
			}
		}
		//jQuery("#record_edit input[type='checkbox']").checkboxradio();

	}
	else{
		if(obj.inline==1){
			

			jQuery('#'+obj.id+"_"+id_record).html(form);
	
			//var hhh=document.getElementById(obj.id+"_"+id_record).outerHTML;
			//console.log(form);
			//jQuery('#txt'+obj.id+"_"+id_record).val(hhh);
			//alert(jQuery('#'+obj.id+"_"+id_record).parent().html());;
		}
		else{
			jQuery('#'+obj.id+"_form").html(form);
		}
  }


	jQuery("#"+obj.id+"_subtable_"+id_record).remove();
	
	if(act=='upd' && obj.aForm.sub_table){
		if(obj.aForm.sub_table.length>0){
			sub=obj.aForm.sub_table[0];

			var sub_html="";
			sub_html+="<h3>"+sub.label+"</h3>";
			sub_html+="<div id='"+obj.id+"_subtable_"+id_record+"_"+sub.id_table+"'></div>";


			if(obj.inline==1){
				jQuery('#'+obj.id+"_"+id_record).after("<tr class='dbmng_subtable' id='#"+obj.id+"_subtable' ><td class='dbmng_subtable' colspan='"+jQuery('#'+obj.id+"_"+id_record).children('td').length+"'>"+sub_html+"</td></tr>");
			}
			else{
				jQuery('#'+obj.id+"_form").append("<div id='#"+obj.id+"_subtable_"+id_record+"'>"+sub_html+"</div>");
			}

			
			if(obj.child[id_record]){
				obj.child[id_record].clearJStorage();
			}


				
			var id_parent=(item.record[obj.aForm.primary_key[0]]);
			console.log("ITEM: "+id_parent);

			obj.child[id_record]  = new Dbmng(sub.id_table, {
				"ajax_url": obj.aParam.ajax_url,
				"div_element": obj.id+"_subtable_"+id_record+"_"+sub.id_table,
				"auto_sync": obj.aParam.auto_sync,
				"inline": 1,
				"auto_edit":obj.aParam.auto_edit,
				"mobile":obj.aParam.mobile,
				"id_parent":id_parent,
				"fk": sub.fk
			});

			obj.child[id_record].start();	


		}
	}

	///The form has been created. We should hide the label in checkbox needed only for mobile app
	if( obj.mobile != 1 ){

		jQuery('label.dbmng_checkbox_label').hide();
	}

	
	jQuery('#'+obj.id+"_"+id_record+"_insert").unbind().click(function(){			

		if(obj.validateForm(id_record)){
			obj.prepareInsert(id_record);		
		}
	});
	

  //console.log("Oggetto "+obj.id);
	//console.log("!!!!!!!qqqbind: #"+obj.id+"_"+id_record+"_update");

	jQuery("#"+obj.id+"_"+id_record+"_update").unbind().click(function(){
			if(obj.validateForm(id_record)){
				obj.prepareUpdate(id_record);		
			}
	});


	jQuery('#'+obj.id+"_"+id_record+"_delete").unbind().click(function(){		
			var r=confirm(t('Do you confirm?'));
			if (r==true){
				obj.deleteRecord(id_record); 
			}		
	});

	jQuery('#'+obj.id+"_"+id_record+"_restore").unbind().click(function(){		
			obj.restoreRecord(id_record); 
			obj.goBackToTable();
	});
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.prepareInsert
// ======================
/// check if it has been compiled the required field
/**
*/
Dbmng.prototype.validateForm = function(id_record){
	var obj=this;
	var ok=true;
	var msg='';
	jQuery.each(obj.aForm.fields, function(index, field){ 
		if(	typeof field.nullable != 'undefined' && field.nullable == 0 ){

			var val= jQuery('#'+obj.id+'_'+id_record+'_'+index).val();
			console.log(" validate "+val+" "+field.label);
			if( val==''){
				msg+=t('The field')+" "+t(field.label)+" "+t('can not be empty')+". ";
				ok=false;
			}
		}
	});

	if(!ok){
		alert(msg);
	}

	return ok;
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.prepareInsert
// ======================
/// create the record and lunch prepareInsert
/**
*/
Dbmng.prototype.prepareInsert = function(id_record){
	var obj=this;
	var record= {};			
	jQuery.each(obj.aForm.fields, function(index, field){ 
		if(typeof window["dbmng_"+field.widget+"_prepare_val"] == "undefined") {
		  record[index] = dbmng_widget_prepare_val(obj.id, id_record, index); 
		}
		else{
			record[index] =  executeFunctionByName("dbmng_"+field.widget+"_prepare_val", window, obj.id, id_record, index);
		}								
	});
	obj.insertRecord({ 'state':'ins', 'record': record} , id_record);
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.prepareUpdate
// ======================
/// create the record and lunch prepareUpdate
/**
*/
Dbmng.prototype.prepareUpdate = function(id_record){
	var obj=this;
	var it=(obj.aData.records[id_record]);
	
	jQuery.each(obj.aForm.fields, function(index, field){ 
		if( ! dbmng_check_is_pk(field) ) 	{
			if(typeof window["dbmng_"+field.widget+"_prepare_val"] == "undefined") {
				it.record[index] = dbmng_widget_prepare_val(obj.id, id_record, index); 
			}
			else{
			  it.record[index] =  executeFunctionByName("dbmng_"+field.widget+"_prepare_val", window, obj.id, id_record, index);
			}
		}
	});
	if(it.state!='ins'){
		it.state='upd';
	}
	obj.updateRecord(it, id_record);
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.layout_form_widget
// ======================
/// The function add an input widget
/**
*/
Dbmng.prototype.layout_form_widget = function( fld, field, id_record, value, more, act ){	
	var obj=this;
	var html='';
	if(typeof window["dbmng_"+field.widget+"_form"] == "undefined") {
		html = dbmng_widget_form(obj.id, fld, field, id_record, value, more, act ); 
	}
	else{
	  html =  executeFunctionByName("dbmng_"+field.widget+"_form", window, obj.id, fld, field, id_record, value, more, act );
	}

	return html;
}

/////////////////////////////////////////////////////////////////////////////
// Dbmng.layout_get_nullable
// ======================
/// The function get from metadb the nullable value for a specific fields
/**
*/
Dbmng.layout_get_nullable = function( field, act ){
	ht = "";
	if(	typeof field.nullable != 'undefined' && field.nullable == 0 )
		ht += "required='required' ";
			
	/* Check if needed
	if( typeof act != 'undefined' )
		{
			if( act != "search" && act != "do_search" )
				{
					if(	typeof field.nullable != 'undefined' && field.nullable == 0 )
							ht += "required ";
				}
		}
		*/
	return ht;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_check_is_pk
// ======================
/// The function check if a field is a primary key
/**
*/
function dbmng_check_is_pk(fld_value){
	var ret=false;
	if( typeof fld_value.key == 'undefined' ){
		ret = false;
	}
	else if( (parseInt(fld_value.key) == 1 || parseInt(fld_value.key) == 2) ){
		ret = true;
	}

	return ret;
}


/////////////////////////////////////////////////////////////////////////////
// layout_view_field_table
// ======================
/// The function check if a field must be show in table view
/**
*/
function layout_view_field_table(fld_value){
	ret=true;	
	if (typeof fld_value != 'undefined') {
		if(fld_value == 1){
			ret=false;
		}
	}
	return ret;
}

//Creat an object for unique identifier
var Guid = Guid || (function () {

var EMPTY = '00000000-0000-0000-0000-000000000000';

var _padLeft = function (paddingString, width, replacementChar) {
	return paddingString.length >= width ? paddingString : _padLeft(replacementChar + paddingString, width, replacementChar || ' ');
};

var _s4 = function (number) {
	var hexadecimalResult = number.toString(16);
	return _padLeft(hexadecimalResult, 4, '0');
};

var _cryptoGuid = function () {
  var buffer = new window.Uint16Array(8);
  window.crypto.getRandomValues(buffer);
  return [_s4(buffer[0]) + _s4(buffer[1]), _s4(buffer[2]), _s4(buffer[3]), _s4(buffer[4]), _s4(buffer[5]) + _s4(buffer[6]) + _s4(buffer[7])].join('-');
};

var _guid = function () {
  var currentDateMilliseconds = new Date().getTime();
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (currentChar) {
    var randomChar = (currentDateMilliseconds + Math.random() * 16) % 16 | 0;
    currentDateMilliseconds = Math.floor(currentDateMilliseconds / 16);
    return (currentChar === 'x' ? randomChar : (randomChar & 0x7 | 0x8)).toString(16);
  });
};

var create = function () {
  var hasCrypto = typeof (window.crypto) != 'undefined';
	var hasRandomValues=false;
	if(hasCrypto){
	  hasRandomValues = typeof (window.crypto.getRandomValues) != 'undefined';
	}
  return (hasCrypto && hasRandomValues) ? _cryptoGuid() : _guid();
};

return {
  newGuid: create,
  empty: EMPTY
};})();


// Create a dialog and display it
function showMessageBox (message) {
	
	//console.log(message);
	if(jQuery.mobile)	{
		// Create it in memory
		var dlg = jQuery("<div />")
			  .attr("data-role", "dialog")
			  .attr("id", "dialog");
		var content = jQuery("<div />")
			  .attr("data-role", "content")
			  .append(jQuery("<span />").html(message));
		content.append("<a href=\"javascript:jQuery('.ui-dialog').dialog('close'); " +
			  "return false;\" data-role=\"button\" data-rel=\"back\">"+t('Close')+"</a>");
	
		dlg.append(content);
	
		dlg.appendTo(jQuery.mobile.pageContainer);
	
		// show the dialog programmatically
		jQuery.mobile.changePage(dlg, {role: "dialog"});
	}
	else{
		alert(message);	
	}
}



// Create a dialog and display it
function dialogStart (message) {
	
	//console.log(message);
	if(jQuery.mobile)	{


		if(jQuery('#dialog_save'))
			jQuery('#dialog_save').remove();


		// Create it in memory
		var dlg = jQuery("<div />")
			  .attr("data-role", "dialog")
			  .attr("id", "dialog_save");
		var content = jQuery("<div />")
			  .attr("data-role", "content")
			  .append(jQuery("<span />").html("<div id=\"dialog_save_content\">"+message+"</div>"));

		content.append("<a id=\"dialog_save_close\" style=\"display:none\"  href=\"javascript:jQuery('.ui-dialog').dialog('close'); " +
			  "return false;\" data-role=\"button\" data-rel=\"back\">"+t('Close')+"</a>");
	
		dlg.append(content);
	
		dlg.appendTo(jQuery.mobile.pageContainer);
	
		// show the dialog programmatically
		jQuery.mobile.changePage(dlg, {role: "dialog"});
	}
	else{
		alert(message);	
	}
}


function dialogAppend(message, is_mobile){
	try{
		console.log(message);
		if(is_mobile)
			jQuery("#dialog_save_content").append('<br></br>'+message);
		}
	catch(e){
		console.log(e);
	}
}


function dialogClose(){
	jQuery("#dialog_save_close").show();
}



function dbmng_export_table(id, separator)
{
	if(typeof separator == 'undefined')
		separator = ";";
	
	var csv='';
	jQuery.each( jQuery(id+' tr'), function(k,v){
			v = jQuery(v);
			
			if(v.css('display')!='none'){
							
				jQuery.each( v.children('td') , function(k2,v2){
					csv+=jQuery(v2).text()+separator;
				}); //end of col

				csv+='\n';
			}
		} ); //end of row
	console.log(csv);

	//var encodedUri = encodeURI(csv);
	//window.open(encodedUri);

	var pom = document.createElement('a');
  pom.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv));
  pom.setAttribute('download', 'export.csv');
  pom.click();


	
/*
	var link = document.createElement("a");
	link.setAttribute("href", encodedUri);
	link.setAttribute("download", "my_data.csv");

	link.click(); // This will download the data file named "my_data.csv".
*/

}





if (typeof String.prototype.startsWith != 'function') {
  // see below for better implementation!
  String.prototype.startsWith = function (str){
    return this.indexOf(str) == 0;
  };
}

if (typeof String.prototype.endsWith != 'function') {
	String.prototype.endsWith = function(suffix) {
		  return this.indexOf(suffix, this.length - suffix.length) !== -1;
	};
}

/*
 * Return true/false indicating whether we're running under Cordova/Phonegap
 */
function is_cordova() {
    return (typeof(cordova) !== 'undefined' || typeof(phonegap) !== 'undefined');
};
