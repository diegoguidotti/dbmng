<?php


function _climasouth_spider(){

	$html='';

	$mode='edit';

	if(isset($_REQUEST['spider_mode'])){
		$mode=$_REQUEST['spider_mode'];
	}


		

	if(false){



			$html.='<style>.dbmng_form_field_current_rating , .dbmng_form_field_target_rating { width:50px;} .dbmng_form_field_id_c_country  { width:80px;} 
				.dbmng_form_field_id_c_assessment {width:300px;}
			</style>';

			$html.='<a class="btn" href="?spider_mode=view">'.t('Generate the Chart').'</a>';

			$id_table ="19";

						
/*			// Prepare aParam array
			$aParam                             = array();
			$aParam['hidden_vars']              = array();
			//$aParam['hidden_vars']['iddec']       = $id_table;
			//$aParam['theme']		= 'bootstrap';
			$aParam['base_path'] = base_path();
			$aParam['user_function']['del'] = 0;    
			$aParam['user_function']['dup'] = 0;    
			 $aParam['user_function']['prt_rec'] = 0;	      
	  $aParam['user_function']['prt_tbl'] = 0;  


			$aForm    = dbmng_get_form_array(($id_table));
							
			$html .= dbmng_crud($aForm, $aParam);
*/


 $html.="<script>


function spider_module_search(id)
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

</script>";


			$html .= "<script>jQuery(document).ready(function(){spider_module_search('spider_ind_container');});</script>";

			$html .= "<div class='row'>";
			$html .= "<div class='col-sm-6'></div>";
			$html .= "<div class='col-sm-6' style='text-align:right'><label>".t('Search')."</label>:&nbsp;<input type='text' id='dbmng_search' /></div>";	//class='form-control input-sm' 
			$html .= "</div>";



			$html.='<div id="spider_ind_container"></div>';

			$aParam['div_element']          = 'spider_ind_container';    
			$aParam['inline']               = 1;    
			$aParam['auto_edit']            = 0;    
			$aParam['user_function']['del'] = 0;    

			$html.=dbmng_crud_js( $id_table, $aParam );


	}
	else{
		
/*
			$html.='<a class="btn" href="?spider_mode=edit">'.t('Edit the data').'</a> - ';

			$html.='<a class="btn" href="?spider_mode=edit">'.t('Archive the Simulation').'</a> - ';

			$html.='<a class="btn" href="?spider_mode=edit">'.t('Start a new Simulation').'</a>';
*/

		$html.='<style>

				#spider_selector{					
					text-align: center;
					margin: 10px;
				}

				#spider_selector select{
					font-size: 1em;
					height: 2em;
					width: 400px;
					padding: 4px;
					margin: 4px;
		
				}

				#spider_chart{
					/*border: 1px solid #CCC;*/
				}
				#spider_chart_legend{
					float:left;
				}
				#rating_legend{
					float: right;
					width: 701px;
				}

				#spider_chart_legend {
					margin-right: 20px;					
				}


				table.rating_legend td{
					text-align: left;
				}

				#spider_chart_legend li{
					text-align:left;			
				}

				#spider_chart_legend li span{
					width:60px;					
					display: block;
					float:left;
          margin-right: 10px;
				}

				#spider_chart_legend li{
					list-style: none;		
          
				}

				#content{
					background: #FFF;
				}

				#spider_legends{
					margin-top: 25px;
				}

			</style>';	


		$html.='<div id="intro">
A flexible and participatory tool has been developed to effectively identify, simulate and prioritise interventions. The situation of a country is assessed and scored from ‘1’ to 5’,  with respect to particular topics and potential intervention areas. The results obtained are converted into a ‘spider chart’ where possible intervention areas are visualised on the spokes of the diagram. The application of the tool suggests a direction for priority interventions that will contribute more effectively to ‘stretching the web’, and smoothen out the indentures in the graph. The tool can also be used for monitoring and evaluating the performance and impact of the project across intervention areas and topics.</div>';

		$html.='<div id="spider_selector"><table class="table"><tr><th colspan="2">Simulation 1</th><tr><tr><td>Assessment Topic</td><td>Country</td></tr><tr>';


			$html.='<td><select onChange="changeSector()" id="choose_a_sector"><option>Choose an assessment topic</option></select></td>';
			$html.='<td><select onChange="changeCountry()" id="choose_a_country"><option>Choose a country</option></select></td>';
			$html.="</tr></table>";



			$html.='<ul id="myTab" class="nav nav-tabs" role="tablist">';
				$html.='<li role="presentation" class="active"><a href="#chart" id="chart-tab" role="tab" data-toggle="tab" aria-controls="chart" aria-expanded="true">Chart</a></li>';
				$html.='<li role="presentation" ><a href="#data" id="data-tab" role="tab" data-toggle="tab" aria-controls="data" aria-expanded="true">Data</a></li>';
			$html.='</ul>';
    

		$html.='<div class="tab-content">';

			$html.='<div role="tabpanel" class="tab-pane active" id="chart" aria-labelledby="chart-tab"><div id="radar_container"></div></div>';
			$html.='<div role="tabpanel" class="tab-pane " id="data" aria-labelledby="data-tab"><div id="spider_ind_container"></div></div>';
      
		$html.='</div>';

		$html.="
			<style>
			.tab-content .active{
				display:block !important; 
			}
			</style>
		";


			//$html .= "<script>jQuery(document).ready(function(){spider_module_search('spider_ind_container');});</script>";

		



			$html.='';
			$id_table ="19";

			$aParam['div_element']          = 'spider_ind_container';    
			$aParam['inline']               = 1;    
			$aParam['auto_edit']            = 0;    
			$aParam['user_function']['del'] = 0;    
			$aParam['user_function']['insert'] = 0;    
			$aParam['jsHook']['create_table_end'] = 'spider_select';    

			$html.=dbmng_crud_js( $id_table, $aParam );




		$html.='<script src="'.base_path().'/sites/all/modules/climasouth_video/climasouth_video.js"></script>';
		$html.='<script src="'.base_path().'/sites/all/modules/climasouth_video/js/Chart.min.js"></script>';


		$html.='<style>#spider_ind_container .dbmng_form_field_current_rating , #spider_ind_container  .dbmng_form_field_target_rating { width:50px;} #spider_ind_container  .dbmng_form_field_id_c_country  { width:80px;} 
				.dbmng_form_field_id_c_assessment {width:300px;}

				#dbmng_19_spider_ind_container_add{display:none;}

				.dbmng_table_label{display:none;}

				#dbmng_19_spider_ind_container_table td{
					text-align: left;
				}
			
			</style>';


		$html.='
			<!--[if lte IE 8]>
		      <script src="'.base_path().'/sites/all/modules/climasouth_video/js/excanvas.js"></script>
		  <![endif]-->
		';

		$html.='<script>var base_path="'.base_path().'"; createSpiderChart(true);</script>';


	}


	return $html;

}
