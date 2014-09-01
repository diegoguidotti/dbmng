<?php

function _climasouth_prj_workspace()
{
	$html = "";
	if( !isset($_REQUEST['act']) )
		{
			$html .= "<ul>";
			$html .= "<li><a href='?act=ins&tbl=c_resource'>" . t("Insert a new project workspace") . "</a></li>";
			$html .= "<li><a href='?act=view&tbl=c_resource'>" . t("Consult project workspace") . "</a></li>";
			$html .= "</ul>";
		}
	else
		{
			$_REQUEST['tbl'] = 'c_resource';
			$typeres = "project";
			$html .= _climasouth_workspace($typeres);
		}
	return $html;
}

function _climasouth_workspace($typeres = null, $edit_id = null) 
{
	dbmng_add_drupal_libraries();



	$html='';
	global $user;

	$table_name="c_resource";

	if ($user->uid) 
		{

			if( isset($table_name) )
				{


					//get the form!!!
					$id_table = $table_name;
					$aForm    = dbmng_get_form_array(($id_table));
					
					if( $id_table == "c_resource" )
						{
							if( !isset($typeres) && $id_table == "c_resource" )
								{
									//$sql = "select id_c_tags, tags from c_tags where tag_type='resource';";
									$where = "tag_type='resource'";
								}
							elseif( isset($typeres) and $typeres == "project" and $id_table == "c_resource" )
								{
									//$sql = "select id_c_tags, tags from c_tags where tag_type='project';";
									$where = "tag_type='project'";
								}
							else
								{
									//$sql = "select id_c_tags, tags from c_tags where tag_type='project';";
									$where = "tag_type='".$typeres."'";
								}

							$sql = "select id_c_tags, tags from c_tags ";//where $where;";
							
							$res = dbmng_query($sql,array());

							$aFVoc = array();
							foreach($res as $val)
								{
									$keys=array_keys((array)$val);
									$aFVoc[$val->$keys[0]] = $val->$keys[1];
								}
							$aForm['fields']['id_c_resource_c_tags']['voc_val'] = $aFVoc;
						}
					//$aFields[$fld->field_name]['voc_val'] = $aFVoc;

						
					//the param array stores some custom variable used by the renderer
					//hidden_vars are some hidden variables used by the form creation
					global $user;
					unset($aParam);
					$aParam                          = array();
					$aParam['filters']               = array();
					$aParam['hidden_vars']           = array();
					$aParam['hidden_vars']['tbl']	   = $table_name; //save the table id
					$aParam['user_function']['dup']	 = 0;	              // allow to enabled=1 or disabled=0 the duplication function
					$aParam['user_function']['prt_rec'] = 0;
					$aParam['user_function']['prt_tbl'] = 0;
					$aParam['tbl_sorter']              = '1';
					$aParam['ui']['btn_name'] = t('Insert new');
					
					if( isset($typeres) and $typeres == "project" and $id_table == "c_resource" )
						{
							$aParam['filters']['is_project_workspace'] = 1;
						}
					// update record

					if(!isset($edit_id)){

						$html .= dbmng_crud($aForm, $aParam);
					}
					else{

						if(!req_equal('act','do_upd') && !req_equal('act','del') ){
							$html .= dbmng_create_form($aForm, $aParam, 1);
						}
						else{
								dbmng_create_form_process($aForm, $aParam);
						}
					}

				}
		}
	else 
		{
			$html.=t('Only for registered users');
		}

	return $html;
}

function  _climasouth_resource_search(){
	dbmng_add_drupal_libraries();
	$html="";
	$show_list=true;

	if(isset($_REQUEST['id_c_resource'])){
		$show_list=false;
		$id_c_resource=$_REQUEST['id_c_resource'];

		//$html.="editing ".$id_c_resource;
		$html.=_climasouth_workspace('resource',$id_c_resource);
		if(req_equal('act','do_upd') || req_equal('act','del')){
				$show_list=true;
		}		

		
	}


	if($show_list){
		
		$html ='<div id="tag_container">&nbsp;</div>';
		$html .='<div id="tag_sel_container">&nbsp;</div>';

		$html .='<div id="resource_search">';
		

		$html.='<div id="resource_search_items">';

		$html.='<form method="POST" action="'.base_path().'climasouth/resource_search">';

		$fs="";


		if(isset($_REQUEST['free_search'])){
			$fs=strtolower($_REQUEST['free_search']);
		}
		
		$html.= '<div style="clear:both; width:100%"><input placeHolder="Search across resources" id="free_search" type="input" name="free_search" value="'.$fs.'" /></div>';

		$html.='<div id="linkToKB"><a href="'.base_path().'navigator">CC Knowledge Navigator</a></div>';
		//$html.='</td><td width="500px" valign="top"><div id="tag_cloud"></div></td></table>';

/*
		$html.=_add_filter(t('Filter by country'), 'id_c_country', "SELECT id_c_country, country_name FROM c_country c WHERE id_c_country in (select id_c_country from c_resource_c_country)  " );
		$html.=_add_filter(t('Filter by language'), 'id_c_language', "SELECT * FROM c_language c WHERE id_c_language in (select id_c_language from c_resource_c_language) " );
		$html.=_add_filter(t('Filter by subject'), 'id_c_subject', "SELECT * FROM c_subject c WHERE id_c_subject in (select id_c_subject from c_resource_c_subject) " );
	
		$html.='<br/><input  type="submit" value="Filter"/>';
*/
		$html.='</form>';

		$html.='</div>';

		

		$html.='<div id="resource_search_res">';

 


		$q = "SELECT c.id_c_resource, file, res_title, res_author, res_description, id_c_visibility, res_tags, res_date, organisation ";
		$q .= ", GROUP_CONCAT(DISTINCT concat(co.country_name)  SEPARATOR '|') as country   ";
		$q .= ", GROUP_CONCAT(DISTINCT concat(s.subject)  SEPARATOR '|') as subject   ";
		$q .= ", GROUP_CONCAT(DISTINCT concat(t.tags)  SEPARATOR '|') as tags   ";
		$q .= ", GROUP_CONCAT(DISTINCT concat(f.file_format)  SEPARATOR '|') as file_format   ";
		$q .= ", GROUP_CONCAT(DISTINCT concat(l.language)  SEPARATOR '|') as language   ";

		$q.=" FROM c_resource c left join c_resource_c_country crc ON crc.id_c_resource=c.id_c_resource LEFT JOIN c_country co ON crc.id_c_country=co.id_c_country ";

		$q.=" LEFT JOIN c_resource_c_subject crs ON crs.id_c_resource=c.id_c_resource LEFT JOIN c_subject s ON crs.id_c_subject=s.id_c_subject ";
		$q.=" LEFT JOIN c_resource_c_tags    crt ON crt.id_c_resource=c.id_c_resource LEFT JOIN c_tags    t ON crt.id_c_tags   =t.id_c_tags ";
		$q.=" LEFT JOIN c_resource_c_file_format crf ON crf.id_c_resource=c.id_c_resource LEFT JOIN c_file_format  f ON crf.id_c_file_format  =f.id_c_file_format ";
		$q.=" LEFT JOIN c_resource_c_language    crl ON crl.id_c_resource=c.id_c_resource LEFT JOIN c_language     l ON crl.id_c_language     =l.id_c_language ";

		$q .= " WHERE true ";

		$q.=_add_condition('id_c_country', 'c_resource_c_country' );
		$q.=_add_condition('id_c_language', 'c_resource_c_language' );
		$q.=_add_condition('id_c_subject', 'c_resource_c_subject' );

		if($fs<>''){
			$q .= "AND (lower(res_description) LIKE '%".$fs."%' OR  lower(res_title) LIKE '%".$fs."%' OR  lower(res_author) LIKE '%".$fs."%' OR  lower(organisation) LIKE '%".$fs."%' OR  lower(res_tags) LIKE '%".$fs."%' ) ";
		}

		$html.="<script>jQuery(document).ready(function(){formatTileResources();});</script>";

		global $user;
		if(in_array('administrator', $user->roles)){
			//shows all
		}
		else if(in_array('experts', $user->roles)){
			$q .= "AND id_c_visibility>=5 ";
		}
		else if(in_array('official', $user->roles)){
			$q .= "AND id_c_visibility>=10 ";
		}
		else if ( $user->uid ) {
			$q .= "AND id_c_visibility>=20 ";
		}
		else{
			$q .= "AND id_c_visibility>=99 ";
		}	
		$q.=" group by c.id_c_resource, file, res_title, res_author, res_description, id_c_visibility, res_tags, res_date, organisation ";
		$q.= "order by c.id_c_resource desc;";

		//echo $q;

		$res = dbmng_query( $q, array() );
		$n=0;
		foreach($res as $r)
			{
				$html.=_climasouth_render_res($r);
				$n++;				
			}
	
		if($n==0){
			if ( !($user->uid)){
				$html.='There are no documents available to non-registered users. Please login.';
			}
			else{
				$html.='There are no documents available.';
			}
		}



		$html.='</div>';

		$html.='</div>';
	}

	return $html;
}


function _add_filter($filter_label, $field_name, $q){	
	$rc=dbmng_query($q,  array() );
	$html="";

	if($rc->rowCount()>0){	

		$html=$filter_label.'<br/><select name="'.$field_name.'"><option value="*">All</option>';
		foreach($rc as $r)
			{
				$r=array_values((array) $r);
				//print_r($r);

				$sel="";			
				if(req_equal($field_name, $r[0])) {
					$sel=' selected = "TRUE" ';
				}

				$html.='<option '.$sel.' value="'.$r[0].'">'.$r[1].'</option>';
			}
		$html.='</select>';
	}
	return $html;
}

function _add_condition($field_name, $table_name){
	$q='';
	if(isset($_REQUEST[$field_name])) {
		if($_REQUEST[$field_name]<>'*'){
			$q.=' AND id_c_resource in (select id_c_resource from '.$table_name.' WHERE '.$field_name.'='.intval($_REQUEST[ $field_name ]).') ';
		}
	}	
	return $q;
}


/*
function _is_equal($val_name, $value) {

	$ret=false;
	if(isset($_REQUEST[$val_name])){
		if($_REQUEST[$val_name]==$value){
			$ret=true;
		}
	}
	return $ret;

}
*/

function _climasouth_render_res($r)
{
	global $user;
	$edit=false;
	if(in_array('administrator', $user->roles)){
		$edit=true;
	}

	$html='<div class="climasouth_res"><div class="climasouth_res_header">';

	if($r->country!=null){
		

		$f=strtok($r->country, "|");
		$flag = '<img src="'.base_path().'docs/'.$f .'.png" />';

		while ($f !== false) {
			$f=strtok("|");
			if($f<>''){
				$flag.= '<img src="'.base_path().'docs/'.$f .'.png" />';
			}
		}

		$html.='<div class="flag">'.$flag.'</div>';
	}

	if($edit){
		$html.='<span class="res_edit"><a href="'.base_path().'/climasouth/resource_search?id_c_resource='.$r->id_c_resource.'">'.t('Edit').'</a></span>';
		$html.='<span class="res_edit"><a onclick="return confirm(\'Are you sure?\')" href="'.base_path().'/climasouth/resource_search?id_c_resource='.$r->id_c_resource.'&act=del">'.t('Delete').'</a></span>';
	}


	$html.='<div class="res_head"><h3>';

	if($r->file!=null){
		$html.='<a target="_NEW" href="'.base_path().'docs/'.$r->file.'">'.$r->res_title.'</a>';
		
	}
	else{
		$html.=$r->res_title;
	}
	$html.='</h3></div>';
	$html.="</div>";


	$html.='<div class="climasouth_res_vars">';
	if($r->res_author!=null)
		$html.="<div class='author'><span class='clabel'>".t('Author').": </span><span class='cvalue'>".$r->res_author."</span></div>";

	if($r->organisation!=null)
		$html.="<div class='organisation'><span class='clabel'>".t('Organisation').": </span><span class='cvalue'>".$r->organisation."</span></div>";

	if($r->res_date!=null)
		$html.="<div class='year'><span class='clabel'>".t('Year').": </span><span class='cvalue'>".$r->res_date."</span></div>";

	if($r->subject!=null){
		$html.="<div class='subject'><span class='clabel'>".t('Subjects').": </span><span class='cvalue'>".str_replace('|', '<span>,</span> ', $r->subject)."</span></div>";
	}

	if($r->country!=null){
		$html.="<div class='country'><span class='clabel'>".t('Countries').": </span><span class='cvalue'>".str_replace('|', '<span>,</span> ', $r->country)."</span></div>";
	}

	if($r->tags!=null){
		$html.="<div class='tags'><span class='clabel'>".t('Tags').": </span><span class='cvalue'>".str_replace('|', '<span>,</span> ', $r->tags)."</span></div>";
	}

	if($r->file_format!=null){
		$html.="<div class='file_format'><span class='clabel'>".t('File Format').": </span><span class='cvalue'>".str_replace('|', '<span>,</span> ', $r->file_format)."</span></div>";
	}

	if($r->language!=null){
		$html.="<div class='language'><span class='clabel'>".t('Language').": </span><span class='cvalue'>".str_replace('|', '<span>,</span> ', $r->language)."</span></div>";
	}

	$html.='</div>';
	$html.='<div class="climasouth_res_des">'.$r->res_description.'</div>';
	$html.="</div>";
	return $html;
}
