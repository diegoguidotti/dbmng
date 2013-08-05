DBMNG
=====


DBMNG is a proposed library to Create Read Update and Delete a database table. 


To generate the interface of an existing table you need to define in an associative array with few metadata:

``` php
$aForm=array(  
  'table_name' => 'test' ,
	'primary_key'=> array('id_test'), 
	'fields'     => array(
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
  $aParam['user_function']['dup']    = 1;	        // record duplication
  $aParam['user_function']['ins']	   = 1;	        // insert
  $aParam['user_function']['upd']	   = 1;         // update
  $aParam['user_function']['del']	   = 1;	        // delete

  //activate a table footer with filter sections
  $aParam['tbl_footer']              = 1;               
  
  //add a custom function to each record calling show_fields=id_record 
  $aParam['custom_function'][0]['custom_variable'] = 'show_fields';
  $aParam['custom_function'][0]['custom_label']    = 'Show Fields';
  
  //order the table records basic on the field indicated in this variable
  $aParam['tbl_order']        = 'field_order';
  
  //allow to add the field sorter functionality
  $aParam['tbl_sorter']        = '1';
  
  //Create the CRUD interface using the custom parameters
  echo dbmng_crud($aForm, $aParam);
```
