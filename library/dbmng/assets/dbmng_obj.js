var DEBUG=true;
function debug(d){
	if(DEBUG){
		console.log(d);
	}
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
            if( $.inArray(opt, requests) > -1 )
	            requests.splice($.inArray(opt, requests), 1);
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
	            $.ajax(requests[0]);
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

	this.inline=0;
	if(p.inline){
		this.inline=p.inline;
	}	

	this.auto_sync=0;
	if(p.auto_sync){
		this.auto_sync=p.auto_sync;
	}	

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
  html += "<div id='"+this.id+"_view'>\n";
	jQuery('#'+this.aParam.div_element).html(html);

	//check when exit the page
	jQuery(window).bind('beforeunload', function(){ 
		if(!obj.isSaved()){
			var msg='Please save before exit.';
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
	obj=this;

	var saved_form=jQuery.jStorage.get(this.id+"_form");
	var saved_data=jQuery.jStorage.get(this.id+"_data");



	if(saved_form && saved_data){
		debug("load from jstore");
		obj.aData =saved_data;
		obj.aForm =saved_form;
		obj.createTable();
	}
	else {
		debug("load from ajax");
		debug(saved_form);
		debug(saved_data);

		
		var form=obj.aForm;
		//form={"id_table":20,"table_name":"mm_test","table_label":"Questa tabella si chiama pippo","fields":{"nome":{}}};

					

		this.am.addReq({ //jQuery.ajax({
			url: this.ajax_url,
			type: "POST",
			data: {"id_table" : obj.id_table, "get_records": true },
			dataType: "json",
			error: function (e) {
				debug(e);
				debug(e.responseText);
			},

			success: function (data) {
				debug(data);	
				if(data.records){
					var newRecords={};
					jQuery.each(data.records,function(k,v){
						newRecords[Guid.newGuid()]={'state':'ok', 'record':v};
					});
					obj.aData = {'records': newRecords};
					obj.aForm = data.aForm;

					//save record and aForm in jStorage
					jQuery.jStorage.set(obj.id+"_form", obj.aForm );
					jQuery.jStorage.set(obj.id+"_data", obj.aData);
					
					obj.createTable();
				}
				else{
					alert('no records returned');
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
	if( obj.mobile == 1 ){
		jQuery('#table_edit_header').html(obj.aForm.table_label);
		//html += "<div data-role='header'><h1>" + obj.aForm.table_name + "</h1></div>\n";
	}
	else{
		html += "<div id='dbmng_table_header'><h1 class='dbmng_table_label'>" + obj.aForm.table_label + "</h1></div>\n";
	}	

	if( obj.mobile == 1 ){
		html+="<ul class='dbmng_list_view' id='"+this.id+"_table' data-role='listview' data-filter='true' >";
		html+="</ul>";
	}
	else { 
		html += "<table class='dbmng_table' id='"+this.id+"_table'>\n";
		//Add header
		html += "<thead>\n";
		html += "<tr >\n";
		jQuery.each(obj.aForm.fields, function(index, field){ 
			var f = field;
			if( layout_view_field_table(f.skip_in_tbl) ){
				html += "<th class='dbmng_field_$fld'>" + t(f.label) + "</th>\n";
			}
		});
		html += "<th class='dbmng_functions'>" + t('actions') + "</th>\n";
		html += "</tr>\n";
		html += "</thead>\n";
		html += "<tbody></tbody></table>";	
	}

	jQuery('#'+obj.id+'_view').html(html);
  
	//Create the body of the table
	jQuery.each(this.aData.records, function(k,value){
		var o = value.record;
		var state = value.state;
		var id_record=k;

		if( obj.mobile == 1 ){
			var html_row = "<li ><a class='dbmng_edit_button' href='#record_edit' ><span id='"+obj.id+"_"+k+"' class='"+state+"'>";
			html_row += obj.createRow(value, id_record);	//<a href = "#">record name</a>
			html_row += "</span></a></li>\n";

			jQuery('#'+obj.id+'_view ul').append(html_row);

		}
		else{
			var html_row = "<tr id='"+obj.id+"_"+k+"' class='"+state+"'>";
	
			html_row += obj.createRow(value, id_record);
			html_row += "</tr>\n";	

			//Save the table row in DOM
			jQuery('#'+obj.id+'_view tbody').append(html_row);
		}

		//attach command assign the click function to delete/update/restore/insert button			
		obj.attachCommand(id_record);
	});




	//Add the insert button
	jQuery('#'+obj.id+'_view').append("<a id='"+obj.id+"_add'>"+t("Add")+"</a>");
	jQuery('#'+obj.id+"_add").click(function(){
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
		jQuery('#'+obj.id+'_view').append(" - <a id='"+obj.id+"_save'>"+t("Save")+"</a>");
		jQuery('#'+obj.id+"_save").click(function(){
			obj.syncData();		
		});	
	}

	jQuery('#'+obj.id+'_view').append(" - <a id='"+obj.id+"_reload'>"+t("Reset")+"</a>");
	jQuery('#'+obj.id+"_reload").click(function(){
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
	      debug(current);
	      debug(current.length);
	
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
			jQuery('#'+this.id+'_table').listview().listview('refresh');
			debug('ok refresh');
		}
		catch(e){debug(e);
			debug('err refresh');
			
		}
	}


	debug("createTable - end");
	//return html;
};


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.resetDb
// ======================
/// Populate the table using aData
/**
*/
Dbmng.prototype.resetDb = function() {
	jQuery.jStorage.deleteKey(obj.id+"_data");
	jQuery.jStorage.deleteKey(obj.id+"_form");
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
	var q=jQuery(document).queue('myAjaxQueue', function() {
		obj.running=true;

		if(obj.isSaved()){
			debug('NO DATA TO SAVE');
			obj.running=false;
		}
		else {
			obj.prog = obj.prog+1;
			debug('start '+obj.prog);
			debug(JSON.stringify(obj.aData.inserted));

			//TODO: check if it is better to use ajaxmanager
			//obj.am.addReq({ //
			jQuery.ajax({
				url: obj.ajax_url,
				type: "POST",
				data: {"id_table" : obj.id_table, "inserted":  JSON.stringify(obj.aData.inserted), "deleted": JSON.stringify(obj.aData.deleted) , "updated": JSON.stringify(obj.aData.updated) }, 
				dataType: "json",
				success: function (data) {
					if(data.deleted){
						jQuery.each(data.deleted, function(k,v){
							if(v.ok==1){
								delete obj.aData.records[k];
								delete obj.aData.deleted[k];							
							}
							else{
								obj.aData.records[k].error=v.error;
							}
						});
					}

					var pk_key=obj.aForm.primary_key[0];

					if(data.inserted){
						jQuery.each(data.inserted, function(k,v){
							if(v.ok==1){
								obj.aData.records[k].state='ok';
								obj.aData.records[k].record[pk_key] = v.inserted_id;
								delete obj.aData.inserted[k];	
							}
							else{														
									//obj.aData.records[k].error=v.error;
							}
						});
					}

					if(data.updated){
						jQuery.each(data.updated, function(k,v){
							if(v.ok==1){
								if(obj.aData.records[k]){
									obj.aData.records[k].state='ok';							
									delete obj.aData.updated[k];	
								}
								else{
									alert('Record '+k+' not found in updated');
								}
							}
							else{							
								debug(v);
								//obj.aData.records[k].error=v.error;
							}
						});
					}

					obj.createTable();
					//jQuery.jStorage.deleteKey(obj.id+"_data");

					debug('end '+obj.prog);
					obj.running=false;

					//end of Success	
				},
				error: function (xhr, ajaxOptions, thrownError){
			  	debug(xhr);
					obj.running=false;
		  	 //debug(thrownError);
			  }   
			});	//end of Ajax
		}
	}); //end of queue

	if(!obj.running){
	  jQuery(document).dequeue('myAjaxQueue'); 
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

	jQuery('#'+obj.id+'_del_'+id_record).click(function(e){		
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

	jQuery('#'+obj.id+'_restore_'+id_record).click(function(e){						
		obj.restoreRecord(id_record);
		e.stopPropagation();
	});

	jQuery('#'+obj.id+'_upd_'+id_record).click(function(e){						
		obj.createForm(id_record);			
	});
	
	if( obj.mobile == 1 ) {
		jQuery('#'+obj.id+'_edit_'+id_record).click(function(e){						
			obj.createForm(id_record);			
		});
	}
	
	jQuery('#'+obj.id+'_dup_'+id_record).click(function(e){						
		obj.duplicateRecord(id_record);
		e.stopPropagation();
	});
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

	var added_field_mobile=0;
	debug(value.record);

	var html_row="";

		for( var key in o )	{        
			//get the field parameters
      var f = obj.aForm.fields[key];
      if(f){
				if( layout_view_field_table(f.skip_in_tbl) ){
					if (o.hasOwnProperty(key)){
						var field_value=o[key];
						var html_value='';

						if(typeof window["dbmng_"+f.widget+"_html"] == "undefined") {
							html_value = dbmng_widget_html(field_value, f ); 
						}
						else{
							html_value =  executeFunctionByName("dbmng_"+f.widget+"_html", window, field_value, f );
						}

						if(obj.mobile){							
							if(added_field_mobile<2){
								html_row += html_value +" ";
								added_field_mobile++;
							}
						}	
						else{
							html_row += "<td>" + html_value + "</td>";
						}
					}
					else{
						html_row += "<td>-</td>";
					}				
				}
			}
			else{
				debug('field '+key+' not found in aForm' );
			}
		}
	
	// available functionalities
	if( obj.mobile == 1 ){
		//html_row += '<span id="'+obj.id+'_edit_'+id_record+'"><a  class="dbmng_edit_button" href="#record_edit"  >' + t('Edit') +'</a>' + "&nbsp;</span>";
	
//		html_row = '<a class="dbmng_edit_button" href="#record_edit"><span id="'+obj.id+'_edit_'+id_record+'">' + html_row +'</span></a>';

		//html_row = '<span id="'+obj.id+'_edit_'+id_record+'"><a  class="dbmng_edit_button" href="#record_edit"  >' +  html_row +'</a>' + "</span>"; 
		html_row = '<span id="'+obj.id+'_edit_'+id_record+'">' +  html_row + "</span>"; 

	}
	else{
		html_row += "<td class='dbmng_functions'>";

		var nDel=1; nUpd=1; nDup=1;

		if(obj.aParam['user_function']){
			nUpd = ((obj.aParam['user_function']['upd']) ? obj.aParam['user_function']['upd'] : 1);
			nDel = ((obj.aParam['user_function']['del']) ? obj.aParam['user_function']['del'] : 1);
			nDup = ((obj.aParam['user_function']['dup']) ? obj.aParam['user_function']['dup'] : 1);
		}

		if(state=='del'){
				html_row += '<span id="'+obj.id+'_restore_'+id_record+'"><a  class="dbmng_restore_button"  >' + t('Restore') +'</a>' + "&nbsp;</span>";
		}
		else {
						
				if( nDel == 1 ){				
					html_row += '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button"  >' + t('Delete') +'</a>' + "&nbsp;</span>";
				}
				if( nUpd == 1 ){				
					html_row += '<span id="'+obj.id+'_upd_'+id_record+'"><a  class="dbmng_update_button"  >' + t('Update') +'</a>' + "&nbsp;</span>";
				}
				if( nDup == 1 ){				
					html_row += '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";
				}
			
		} 

		if(value.error){
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
		jQuery('#'+obj.id+"_"+id_record).html('');
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
		jQuery("#"+obj.id+"_table").append("<tr id='"+obj.id+"_"+new_id_record+"' class='ins'></tr>");
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
		if(!this.inline){
			jQuery("#"+obj.id+"_view").show();	
			jQuery("#"+obj.id+"_form").hide();
		}

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
		
		if( obj.mobile == 1 ){
		  jQuery.mobile.changePage("#table_edit");
		  
			jQuery("#"+obj.id+"_view").trigger('updatelayout');	
			jQuery("#"+obj.id+"_view").show();	
		}
		else if(!this.inline){
			jQuery("#"+obj.id+"_view").show();	
			jQuery("#"+obj.id+"_form").hide();
		}	
		
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
// Dbmng.prototype.updateStorage
// ======================
/// The function update jStorage
/**
*/
Dbmng.prototype.updateStorage = function() {
	debug('upd storage on '+this.id);

	jQuery.jStorage.set(this.id+"_data", this.aData);

	if(obj.auto_sync){
		debug('start_sync');
		db.syncData();
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
	obj=this;
	var act = 'ins';
	
	if(typeof id_record!='undefined'){
		act='upd';
		item=obj.aData.records[id_record];
	}		
	
	debug('create_form'+obj.inline+" "+act);
	
	if(obj.inline==0){
		//hide the table and show the form
		jQuery("#"+obj.id+"_view").hide();	
		jQuery("#"+obj.id+"_form").show();	
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
		jQuery("#"+obj.id+"_table").append("<tr "+cl+" id='"+obj.id+"_"+id_record+"'></tr>");
	}
	
	var form='<form >';
	jQuery.each(this.aForm.fields, function(index, field){ 			
		//debug(index + ": " + dbmng_check_is_pk(field));
		value = '';
		
		var view_field=true;
		if(dbmng_check_is_pk(field)){
			view_field=false;
		}
		else{
			if(!layout_view_field_table(field.skip_in_tbl) && obj.inline==1)
				view_field=false;
		}
	
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
				if(item.record[index]){
					value=item.record[index];							
				}
			}
			form += obj.layout_form_widget(index, field, id_record, value, '', act) + "<br/>";

			if(obj.inline==1){
				form+='</td>';
			}
		}
		else if (dbmng_check_is_pk(field))	{
			form+="<td>&nbsp</td>";
		}
	});

	form+="</form>";
	
	datarole = ""
	if( obj.mobile == 1 ){
		datarole = "data-role='button'";
	}
	
	
	if(act=='ins'){
		form+="<a id='"+this.id+"_"+id_record+"_insert' "+datarole+">"+t("Insert")+"</a>";		
	}
	else{
		form+="<a id='"+this.id+"_"+id_record+"_update' "+datarole+">"+t("Update")+"</a>";		
	}
	
	if( obj.mobile == 1 ){
		html_del = '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button"  >' + t('Delete') +'</a>' + "&nbsp;</span>";
		html_dup = '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";

		jQuery('#record_edit_dup').html(html_dup); //.trigger("create");
		jQuery('#record_edit_del').html(html_del);//.trigger("create");

	  jQuery.mobile.changePage("#record_edit");
		jQuery('#record_edit_container').html(form); //.trigger("create");
	}
	else{
		if(obj.inline==1){
			jQuery('#'+obj.id+"_"+id_record).html(form)
		}
		else{
			jQuery('#'+obj.id+"_form").html(form);
		}
	}
	
	jQuery('#'+obj.id+"_"+id_record+"_insert").click(function(){			
		obj.prepareInsert(id_record);		
	});
	
	jQuery('#'+obj.id+"_"+id_record+"_update").click(function(){
		obj.prepareUpdate(id_record);		
	});
}


/////////////////////////////////////////////////////////////////////////////
// Dbmng.prototype.prepareInsert
// ======================
/// create the record and lunch prepareInsert
/**
*/
Dbmng.prototype.prepareInsert = function(id_record){
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
		ht += "required ";
			
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
  var hasCrypto = typeof (window.crypto) != 'undefined',
      hasRandomValues = typeof (window.crypto.getRandomValues) != 'undefined';
  return (hasCrypto && hasRandomValues) ? _cryptoGuid() : _guid();
};

return {
  newGuid: create,
  empty: EMPTY
};})();


