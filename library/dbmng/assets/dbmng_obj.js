
/*  General class to visualize and edit a single table
*/
function Dbmng(f , p) {

  this.aForm = f;
 
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
								console.log('manda');
								console.log(requests[0]);
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
	if(p.auto_edit){
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

	
	this.id='dbmng_'+f.table_name;
	if(this.aParam.div_element){
		this.id+='_'+this.aParam.div_element;
	}

	console.log(this.id);
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


//search for the data to the server and, if founded, create the table
Dbmng.prototype.start = function()
{
	obj=this;

	var saved_data= jQuery.jStorage.get(this.id+"_data");
	if(saved_data){
		obj.aData =saved_data;
		obj.createTable();
	}
	else {
				var form=obj.aForm;
				//form={"id_table":20,"table_name":"mm_test","table_label":"Questa tabella si chiama pippo","fields":{"nome":{}}};
			
			this.am.addReq({ //jQuery.ajax({
				url: this.ajax_url,
				type: "POST",
				data: {"aForm" : JSON.stringify(form), "get_records": true },
				dataType: "json",
				error: function (e) {
					console.log(e);
					console.log(e.responseText);
				},
				success: function (data) {

					console.log(data);	
					if(data.records){

						var newRecords={};
						jQuery.each(data.records,function(k,v){
							newRecords[Guid.newGuid()]={'state':'ok', 'record':v};
						});
						obj.aData = {'records': newRecords};					
						obj.createTable();

					}
					else{
						alert('no records returned');
						console.log(data);
					}
				}
		});
	}

}


//Populate the table using aData
Dbmng.prototype.createTable = function()
{	
	//show the table and hide the form
	jQuery("#"+this.id+"_view").show();	
	jQuery("#"+this.id+"_form").hide();	

	//store the object to refer to it in the subfunction
	var obj=this;

	//Create the two container for the table and the form
	
	var html='';
	if( obj.mobile == 1 ){
		html += "<div data-role='header'><h1>" + obj.aForm.table_name + "</h1></div>\n";
	}else{
		html += "<div id='dbmng_table_header'><h1 class='dbmng_table_label'>" + obj.aForm.table_name + "</h1></div>\n";
	}	
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
	html+= "<tbody></tbody></table>";	

	jQuery('#'+obj.id+'_view').html(html);
  
	//Create the body of the table
	jQuery.each(this.aData.records, function(k,value){

			var o = value.record;
			var state = value.state;
			var id_record=k;

			var html_row = "<tr id='"+obj.id+"_"+k+"' class='"+state+"'>";

			html_row += obj.createRow(value, id_record);
			html_row += "</tr>\n";	

		//Save the table row in DOM
		jQuery('#'+obj.id+'_view tbody').append(html_row);

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
		jQuery('#'+obj.id+'_view').append(" - <a id='"+obj.id+"_reload'>"+t("Reset")+"</a>");
	
		jQuery('#'+obj.id+"_save").click(function(){
			obj.syncData();		
		});
	
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
	}


	if(this.auto_edit && this.inline){

			jQuery("#"+obj.id+"_table tr").on('click', function (e) {  
        var el=jQuery(this);        

        if(el.hasClass('working')){
             ; //keep working...
        }
        else{             

            var current=jQuery('#'+obj.id+"_table tr.working");
            console.log(current);
            console.log(current.length);

            if(current.length>0){ //if exist an editing record save it
                console.log('save the record '+current.attr('id'));   
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
            console.log('Start editing record '+this.id);
        }        
    }); 
	}
	
	return html;
};


//Populate the table using aData
Dbmng.prototype.resetDb = function() {
	jQuery.jStorage.deleteKey(obj.id+"_data");
	obj.start();		
}


//Populate the table using aData
Dbmng.prototype.syncData = function()
{	

	var obj=this;
	var q=jQuery(document).queue('myAjaxQueue', function() {

		obj.running=true;

		if(obj.isSaved()){
			console.log('NO DATA TO SAVE');
			obj.running=false;
		}
		else {
			
			obj.prog = obj.prog+1;
			console.log('start '+obj.prog);
			console.log(JSON.stringify(obj.aData.inserted));

			//TODO: check if it is better to use ajaxmanager
			//obj.am.addReq({ //
			jQuery.ajax({
				url: obj.ajax_url,
				type: "POST",
				data: {"aForm" : JSON.stringify(obj.aForm), "inserted":  JSON.stringify(obj.aData.inserted), "deleted": JSON.stringify(obj.aData.deleted) , "updated": JSON.stringify(obj.aData.updated) }, 
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
								console.log(v);
	//							obj.aData.records[k].error=v.error;
							}
						});
					}

					obj.createTable();
					jQuery.jStorage.deleteKey(obj.id+"_data");


				
					console.log('end '+obj.prog);
					obj.running=false;

					//end of Success	
				},
				error: function (xhr, ajaxOptions, thrownError){
			  	console.log(xhr);
					obj.running=false;
		  	 //console.log(thrownError);
			  }   
			});	//end of Ajax
		}
	}); //end of queue

	if(!obj.running){
      jQuery(document).dequeue('myAjaxQueue'); 
  }
}

Dbmng.prototype.attachCommand = function (id_record) 
	{
		var obj=this;
		jQuery('#'+obj.id+'_del_'+id_record).click(function(e){							
			obj.deleteRecord(id_record); 
			e.stopPropagation(); //stopPropagation block the auto_edit features when clicking on table row
		});
		jQuery('#'+obj.id+'_restore_'+id_record).click(function(e){						
			obj.restoreRecord(id_record);
			e.stopPropagation();
		});
		jQuery('#'+obj.id+'_upd_'+id_record).click(function(e){						
			obj.createForm(id_record);			
		});
		jQuery('#'+obj.id+'_dup_'+id_record).click(function(e){						
			obj.duplicateRecord(id_record);
			e.stopPropagation();
		});
}



//check if there are data not synched with the db
Dbmng.prototype.isSaved = function()
{
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

Dbmng.prototype.createRow = function (value, id_record) 
	{
		var obj=this;
		var state=value.state;
		var o=value.record;		
		var html_row='';
			for( var key in o )
				{        
					//get the field parameters
		      var f = obj.aForm.fields[key];
		      if(f){
						if( layout_view_field_table(f.skip_in_tbl) ){
							if (o.hasOwnProperty(key))
							{
								html_row += "<td>" + o[key] + "</td>";
							}
							else{
								html_row += "<td>-</td>";
							}				
						}
					}
					else{
						console.log('field '+key+' not found in aForm' );
					}
			}
		
		// available functionalities
		html_row += "<td class='dbmng_functions'>";
		
		var nDel=1; nUpd=1; nDup=1;

		if(aParam['user_function']){
		  nUpd = ((aParam['user_function']['upd']) ? aParam['user_function']['upd'] : 1);
		  nDel = ((aParam['user_function']['del']) ? aParam['user_function']['del'] : 1);
		  nDup = ((aParam['user_function']['dup']) ? aParam['user_function']['dup'] : 1);
		}

		if(state=='del')
			{
				html_row +=     '<span id="'+obj.id+'_restore_'+id_record+'"><a  class="dbmng_restore_button"  >' + t('Restore') +'</a>' + "&nbsp;</span>";
			}
		else
			{
				if( nDel == 1 )
					{				
						html_row += '<span id="'+obj.id+'_del_'+id_record+'"><a  class="dbmng_delete_button"  >' + t('Delete') +'</a>' + "&nbsp;</span>";
					}
				if( nUpd == 1 )
					{				
						html_row += '<span id="'+obj.id+'_upd_'+id_record+'"><a  class="dbmng_update_button"  >' + t('Update') +'</a>' + "&nbsp;</span>";
					}
				if( nDup == 1 )
					{				
						html_row += '<span id="'+obj.id+'_dup_'+id_record+'"><a  class="dbmng_duplicate_button"  >' + t('Duplicate') +'</a>' + "&nbsp;</span>";
					}
			}
		
		if(value.error){
			html_row += '<span title="'+value.error+'" class="dbmng_error">'+t('Error!')+'</span>';
		}
		html_row += "</td>\n";

		return html_row;
	}


//The function delete one record
Dbmng.prototype.deleteRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	console.log("Find "+id_record+"to delete");
	var to_delete = obj.aData.records[id_record];	
	console.log(to_delete);
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
			console.log('delete record '+id_record);
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



//The function delete one record
Dbmng.prototype.restoreRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	console.log('restore');
	var to_restore = obj.aData.records[id_record];	
	to_restore.state = 'ok';

	console.log(to_restore);
	delete obj.aData.deleted[id_record];		
	jQuery('#'+obj.id+"_"+id_record).removeClass( "del" ).addClass( "ok" );	
	jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(to_restore, id_record));
	//You need to attach again the restore button
	obj.attachCommand(id_record);

	obj.updateStorage();

}			



//The function duplicate one record
Dbmng.prototype.duplicateRecord = function(id_record) {
	//TODO deal with multiple key
	var obj=this;
	
	var new_id_record=[Guid.newGuid()];
	var to_duplicate=jQuery.extend(true, {}, obj.aData.records[id_record]);
	to_duplicate.state = "ins";

	if(to_duplicate)
	{
	 	if(!obj.aData.inserted)
		 	{
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


//The function insert one record
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

		console.log('add record');

		//go back to table
		if(!this.inline){
			jQuery("#"+obj.id+"_view").show();	
			jQuery("#"+obj.id+"_form").hide();
		}


		//Change the id to the temporary one
    jQuery('#'+obj.id+"_"+temporary_id_record).attr("id",obj.id+"_"+id_record);

		console.log(jQuery('#'+obj.id+"_"+id_record));
		jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(item, id_record));	
		console.log('create row');
		jQuery('#'+obj.id+"_"+id_record).removeClass( "ok" ).addClass( "ins" );	
		//You need to attach again the restore button
		obj.attachCommand(id_record);


	}
	else{
		alert('Error. record to insert undefined');
	}		
	obj.updateStorage();
}			

//The function insert one record
Dbmng.prototype.updateRecord = function(item, id_record) {
	//TODO deal with multiple key
	var obj=this;
		
	if(item){
	 	if(!obj.aData.updated){
				obj.aData.updated={};
		}
		obj.aData.updated[id_record]=(item);	

		//go back to table
		if(!this.inline){
			jQuery("#"+obj.id+"_view").show();	
			jQuery("#"+obj.id+"_form").hide();
		}	

		jQuery('#'+obj.id+"_"+id_record).removeClass( "ok" ).addClass( "upd" );
		jQuery('#'+obj.id+"_"+id_record).html(obj.createRow(item, id_record));
		//You need to attach again the restore button
		obj.attachCommand(id_record);
	}
	else{
		alert('Error. record to insert undefined');
	}		

	obj.updateStorage();

}			





Dbmng.prototype.updateStorage = function() {
	console.log('upd storage on '+this.id);

	jQuery.jStorage.set(this.id+"_data", this.aData);


	if(obj.auto_sync){
		console.log('start_sync');
		db.syncData();
	}

	//After update show the main table in not inline
	
}

	

// The function get the label from metadb
Dbmng.layout_get_label = function(field_name, field, act)
{
	lb = field.label;
	if( typeof field.label_long != 'undefined' )
		lb = field.label_long;
	
	sRequired = "";
	if(typeof field.nullable != 'undefined' && field.nullable == 0 )
		sRequired = "<span class='dbmng_required'>*</span>";

	/*	
	if( typeof act != 'undefined' )
		{
			if( act != "search" && act != "do_search" )
				{
					if(typeof field.nullable != 'undefined' && field.nullable == 0 )
						sRequired = "<span class='dbmng_required'>*</span>";
				}
		}
		*/
			
		
	return "<label for='"+field_name+"'>" + t(lb) + " " + sRequired + "</label>";
}


//TODO: review create Form
Dbmng.prototype.createForm = function(id_record) {
		obj=this;
		var act = 'ins';

		if(typeof id_record!='undefined'){
			act='upd';
			item=obj.aData.records[id_record];
		}		

		console.log('create_form'+obj.inline+" "+act);

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
			//console.log(index + ": " + dbmng_check_is_pk(field));
			value = '';
			if( ! dbmng_check_is_pk(field) )
				{
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
			else
				{
					form+="<td>&nbsp</td>";
				}

		});
		form+="</form>";

		if(act=='ins'){
			form+="<a id='"+this.id+"_"+id_record+"_insert'>"+t("Insert")+"</a>";		
		}
		else{
			form+="<a id='"+this.id+"_"+id_record+"_update'>"+t("Update")+"</a>";		
		}
		
		if(obj.inline==1){
			jQuery('#'+obj.id+"_"+id_record).html(form)
		}
		else{
			jQuery('#'+obj.id+"_form").html(form);
		}

		jQuery('#'+obj.id+"_"+id_record+"_insert").click(function(){			
			obj.prepareInsert(id_record);		
		});

		jQuery('#'+obj.id+"_"+id_record+"_update").click(function(){
			obj.prepareUpdate(id_record);		
		});
}


//create the record and lunch prepareInsert
Dbmng.prototype.prepareInsert = function(id_record){

			var record= {};			
			jQuery.each(obj.aForm.fields, function(index, field){ 
				if(typeof window["dbmng_"+field.widget+"_prepare_val"] == "undefined") {
					  record[index] = dbmng_widget_prepare_val(obj.id, id_record, index); 
				}
				else{
					record[index] =  executeFunctionByName("dbmng_"+field.widget+"_prepare_val", window, obj.id, id_record, index);
				}
				/*
				switch( field.widget )
					{
						case "checkbox":
							record[index] = 0;
							if( jQuery('#'+obj.id+'_'+id_record+'_'+index).prop('checked') )
								record[index] = 1;
							break;
						
						default:
							record[index] = jQuery('#'+obj.id+'_'+id_record+'_'+index).val();
							break;
					}
					*/
				
			});
			obj.insertRecord({ 'state':'ins', 'record': record} , id_record);
}


//create the record and lunch prepareUpdate
Dbmng.prototype.prepareUpdate = function(id_record){
		var obj=this;
		var it=(obj.aData.records[id_record]);

		jQuery.each(obj.aForm.fields, function(index, field){ 
			if( ! dbmng_check_is_pk(field) ) 	
				{
					if(typeof window["dbmng_"+field.widget+"_prepare_val"] == "undefined") {
							it.record[index] = dbmng_widget_prepare_val(obj.id, id_record, index); 
					}
					else{
						  it.record[index] =  executeFunctionByName("dbmng_"+field.widget+"_prepare_val", window, obj.id, id_record, index);
					}

				/**
					switch( field.widget )
						{
							case "checkbox":
								it.record[index] = 0;
								if( jQuery('#'+obj.id+'_'+id_record+'_'+index).prop('checked') )
									it.record[index] = 1;
								break;
							
							default:
								it.record[index] = jQuery('#'+obj.id+'_'+id_record+'_'+index).val();
								break;
						}
				*/
				}
		});
		it.state='upd';

		obj.updateRecord(it, id_record);
}

// The function add an input widget
Dbmng.prototype.layout_form_widget = function( fld, field, id_record, value, more, act )
{	
	switch( field.widget )
		{
			// =============== textarea widget =============== //
			case "textarea":
				html  = "<textarea  name='" + fld + "' id='"+this.id+"_"+id_record+"_"+fld+"' ";//.layout_get_nullable($fld_value)." >";
				html += Dbmng.layout_get_nullable(field,act);
				html += " >\n";
				html += value;	
				html += "</textarea>\n";			
				break;
			
			// =============== checkbox widget =============== //
			case "checkbox":
				console.log("incheck");
				console.log(value);
				html = "<input class='dbmng_checkbox' type='checkbox' name='"+fld+"' id='"+this.id+"_"+id_record+"_"+fld+"' ";
			  if( value == 1 || (value != 0 && field.default == 1) )
			    {
						html += " checked='true' ";
					}	
			  //the field will never reply with a null value (true or false)
				//if setted as a non_nullable it will accept only true values
				//$html .= layout_get_nullable($fld_value);	
				html += " />\n";
				break;
				
			// =============== date widget =============== //
			case "date":
				datetime_str='';
			
				//format the date string 
				if( typeof value!="undefined"  && value!='' )
					{
						datetime = new Date(value);             //DateTime::createFromFormat('Y-m-d', $value);
						datetime_str= datetime.toString("d-m-Y"); //datetime->format('d-m-Y');
					}
			
				//add a new input field for the datapicker ui
				html  = "<input type='text' name='"+fld+"_tmp' id='"+fld+"_tmp' value='"+datetime_str+"' />";
				//keep hidden the "real" input form
				html += "<input type='hidden' name='"+fld+"' id='"+fld+"' ";
				html += " value= '"+value+"' ";	
				html += Dbmng.layout_get_nullable(field,act);	
				html += " />\n";
				//html +='<script>  jQuery(function() { jQuery( "#'.$fld.'_tmp" ).datepicker({altField: \'#'.$fld.'\', dateFormat:\'dd-mm-yy\' , altFormat: \'yy-mm-dd\'});  });  </script>';
				break;
			
			// =============== input widget =============== //
			default:
				html  = "<input type='text' name='"+fld+"' id='"+this.id+"_"+id_record+"_"+fld+"' " + more;
				html += " value= '"+value+"' ";	
				html += Dbmng.layout_get_nullable(field,act);
				html += " />\n";
				break;
		}

/*	
	// =============== textarea widget =============== //
	if( field.widget == "textarea" )
		{		
			html = "<textarea  name='" + fld + "' id='"+this.id+"_"+id_record+"_"+fld+"' ";//.layout_get_nullable($fld_value)." >";
			Dbmng.layout_get_nullable(field,act);
			html += " >\n";
			html += value;	
			html += "</textarea>\n";			
		}

	// =============== checkbox widget =============== //
	else if( field.widget == "checkbox" )
		{
		console.log("incheck");
		console.log(value);
			html = "<input class='dbmng_checkbox' type='checkbox' name='"+fld+"' id='"+this.id+"_"+id_record+"_"+fld+"' ";
			//if( value==1 || (value<>0 && field.default == 1) )
		  if( value == 1 || (value != 0 && field.default == 1) )
		    {
					html += " checked='true' ";
				}	
		  //the field will never reply with a null value (true or false)
			//if setted as a non_nullable it will accept only true values
			//$html .= layout_get_nullable($fld_value);	
			html += " />\n";
		}	
	// =============== date widget =============== //
	else if( field.widget == "date" )
		{
			datetime_str='';
		
			//format the date string 
			if( typeof value!="undefined"  && value!='' )
				{
					datetime = new Date(value);             //DateTime::createFromFormat('Y-m-d', $value);
					datetime_str= datetime.toString("d-m-Y"); //datetime->format('d-m-Y');
				}
		
			//add a new input field for the datapicker ui
			html  = "<input type='text' name='"+fld+"_tmp' id='"+fld+"_tmp' value='"+datetime_str+"' />";
			//keep hidden the "real" input form
			html += "<input type='hidden' name='"+fld+"' id='"+fld+"' ";
			html += " value= '"+value+"' ";	
			html += Dbmng.layout_get_nullable(field,act);	
			html += " />\n";
			//html +='<script>  jQuery(function() { jQuery( "#'.$fld.'_tmp" ).datepicker({altField: \'#'.$fld.'\', dateFormat:\'dd-mm-yy\' , altFormat: \'yy-mm-dd\'});  });  </script>';
		}
	// =============== input widget =============== //
	else 
		{
			html  = "<input type='text' name='"+fld+"' id='"+this.id+"_"+id_record+"_"+fld+"' " + more;
			html += " value= '"+value+"' ";	
			Dbmng.layout_get_nullable(field,act);
			html += " />\n";
		}
*/
	return html;
}

Dbmng.layout_get_nullable = function( field, act )
{
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

function dbmng_check_is_pk(fld_value)
{
	var ret=false;
	if( typeof fld_value.key == 'undefined' )
		{
			ret = false;
		}
	else if( (parseInt(fld_value.key) == 1 || parseInt(fld_value.key) == 2) )
		{
			ret = true;
		}

	return ret;
}


function layout_view_field_table(fld_value){
	ret=true;	
	if (typeof fld_value != 'undefined') {
		if(fld_value == 1){
			ret=false;
		}
	}
	return ret;
}

function t(txt){
	return txt;
}

var DEBUG=true;
function debug(d){
	if(DEBUG){
		console.log(d);
	}
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


