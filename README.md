dbmng
=====

La libreria opera principalmente su due array associativi: 
* $aForm e 
* $aParam

L'array $aForm viene generato dinamicamente a partire dalle tabelle di metadb presenti nel database: 
* dbmng_tables e 
* dbmng_fields

L'array $aParam fornisce alla libreria funzionalità aggiuntive. La struttura attuale dell'array è la seguente:

- $aParam                          = array();
- $aParam['filters']               = array();
- $aParam['hidden_vars']           = array();
- $aParam['hidden_vars']['tbl']	   = $_REQUEST['tbl']; //save the table id
- $aParam['hidden_vars']['type_tbl'] = 1;             //table type (1: content table; 2: system table)

//test filter records with a specific uid
``` php
- $aParam['filters']['id_user']	   = $user->uid;      // save the user id
- $aParam['tbl_footer']            = 1;               // allow to add filtering
- $aParam['user_function']['dup']	 = 1;	              // allow to enabled=1 or disabled=0 the duplication function
- $aParam['user_function']['ins']	 = 1;	              // allow to enabled=1 or disabled=0 the insert function
- $aParam['user_function']['upd']	 = 1;               // allow to enabled=1 or disabled=0 the update function
- $aParam['user_function']['del']	 = 1;	              // allow to enabled=1 or disabled=0 the delate function
- $aParam['custom_function'][0]['custom_variable']= 'show_fields';   // allow to add the button show_fields
- $aParam['custom_function'][0]['custom_label']   = t('Show Fields');
``` php
