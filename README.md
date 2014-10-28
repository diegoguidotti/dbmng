DBMNG
=====


DBMNG is a proposed library to Create Read Update and Delete a database table. 


To generate the interface of an existing table you need to define in an associative array with few metadata:

``` php
$aForm=array(  
  'table_name' => 'test' ,
	'primary_key'=> array('id_test'), 
	'fields'     => array(
		'id_test' => array('label'   => 'ID', 'type' => 'int', key => 1 ) ,
		'name' => array('label'   => 'Name', 'type' => 'varchar') ,
		'age'  => array('label'   => 'Age' , 'type' => 'int'    )
	)
);
echo dbmng_crud($aForm);
```

The table metadata can be stored in the database. In that case the CRUD interface can be generated just using 
the key of the desidered table.

``` php
//get the array storing the table metadata from record 1 in table dbmng_tables
$aForm    = dbmng_get_form_array(1); 
echo dbmng_crud($aForm);
``` 

The $aParam value allows to filter the records, add hidden variables to the POST and GET call, define the available 
functions for user and adding custom function


``` php  
  //filter records where the fields contain a specific value
  $aParam['filters']['id_user']      = 1;         //Filter record of user 1


  //add "&lang=en&section=News" to all the call
  $aParam['hidden_vars']['lang']	   = 'en';      
  $aParam['hidden_vars']['section']  = 'News';    

  //define if a specific function is enables (1) or disabled (0)
  //By default all the functionalities are enabled
  $aParam['user_function']['ins']	    = 1;	        // insert
  $aParam['user_function']['upd']	    = 1;          // update
  $aParam['user_function']['del']	    = 1;	        // delete
  $aParam['user_function']['dup']     = 1;	        // record duplication
  $aParam['user_function']['prt_rec'] = 1;	        // export record in pdf format
  $aParam['user_function']['prt_tbl'] = 1;	        // export table in pdf format

  //activate a table footer with filter sections
  $aParam['tbl_footer']               = 1;
  
  //activate the table pagination setting the maximum number of records per page  
  $aParam['tbl_navigation']           = 10;  
  
  //add a custom function to each record calling show_fields=id_record 
  $aParam['custom_function'][0]['custom_variable'] = 'show_fields';
  $aParam['custom_function'][0]['custom_label']    = 'Show Fields';
  
  //to manage the label of the library
  $aParam['ui']['btn_name'] = 'insert the right label'; //replace the insert default button label in the form
  $aParam['ui']['btn_lst_name'] = 'insert the right label'; //replace the add new record label in the table view
  $aParam['ui']['btn_name_search'] = 'insert the right label'; //replace the search default button label in the form
  $aParam['ui']['btn_name_update'] = 'insert the right label'; //replace the update default button label in the form
  $aParam['ui']['fld_separator'] = 0; //Default value 0. If 1, the library add a separater between the fields in the form.
  
  //order the table records basic on the field indicated in this variable
  $aParam['tbl_order']        = 'field_order';
  
  //allow to add the field sorter functionality
  $aParam['tbl_sorter']        = '1';
  
  //specify the directory where the uploaded file will be stored
  //in case this parameter is missing your file will be stored in 'docs/'
  $aParam['file'] = 'sites/docs/';
  
  //gallery parameters
	$aParam['picture']                = 'raw/'; //default directory where your picture will be stored

	$aParam['picture_version']['nrm'] = 'nrm/'; //default directory where normal version is stored automatically by the system
	$aParam['picture_version']['prw'] = 'prw/'; //default directory where preview version is stored automatically by the system
	$aParam['picture_version']['big'] = 'big/'; //default directory where big version is stored automatically by the system
	$aParam['picture_version']['ext'] = 'ext/'; //default directory where extra version is stored automatically by the system

	$aParam['picture_size']['nrm']    = 600; //normal size in pixel
	$aParam['picture_size']['prw']    = 60;  //preview size in pixel
	$aParam['picture_size']['big']    = 900; //big size in pixel
	$aParam['picture_size']['ext']    = 77;  //extra size in pixel
  
  
  //Create the CRUD interface using the custom parameters
  echo dbmng_crud($aForm, $aParam);
```
