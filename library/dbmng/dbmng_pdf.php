<?php
require('sites/all/libraries/fpdf/fpdf.php');

class PDF extends FPDF
{
	function LoadData($sql, $var)
	{
		return dbmng_query($sql, $var);
	}
	
	// Simple table
	function BasicTable($aForm, $aParam, $data)
	{
    // Header
		foreach( $aForm['fields'] as $fld => $fld_value )
			{
				if( layout_view_field_table($fld_value) )
					{
						$this->Cell(40,7,$fld,0);
					}
			}
		$this->Ln();

    // Data
		foreach( $data as $record )
			{
				foreach ( $aForm['fields'] as $fld => $fld_value )
					{
						if( layout_view_field_table($fld_value) )
							{
								if(isset($record->$fld))
									{
										$value=$record->$fld; //dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
									}
								else
									{//TODO: add a comma separated list if widget==multi
										$value.= "&nbsp;";							
									}
								$this->Cell(40,6,$value,1);
							}
					}
				$this->Ln();
			}
	}

	// Better table
	function ImprovedTable($aForm, $aParam, $data)
	{
	    // Column widths
	    $w = array(40, 35, 40, 45);
	    $cellw = 35; //cell width
	    $w = 0;
	    
	    // Header
			foreach( $aForm['fields'] as $fld => $fld_value )
				{
					if( layout_view_field_table($fld_value) )
						{
							$this->Cell($cellw,7,$fld,1,0,'C');
							$w+=$cellw;
						}
				}
			$this->Ln();

	    //for($i=0;$i<count($header);$i++)
	    //    $this->Cell($w[$i],7,$header[$i],1,0,'C');
	    //$this->Ln();
	    
	    // Data
			foreach( $data as $record )
				{
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if( layout_view_field_table($fld_value) )
								{
									if(isset($record->$fld))
										{
											$value=$record->$fld; //dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
										}
									else
										{//TODO: add a comma separated list if widget==multi
											$value.= "&nbsp;";							
										}
									$this->Cell($cellw,6,$value,'LR');
								}
						}
					$this->Ln();
				}
			$this->Cell($w,0,'','T');
	}

	// Colored table
	function FancyTable($aForm, $aParam, $data)
	{
	    // Colors, line width and bold font
	    $this->SetFillColor(255,0,0);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(128,0,0);
	    $this->SetLineWidth(.3);
	    $this->SetFont('','B');
	    
	    // Header
	    $w = array(40, 35, 40, 45);
	    $cellw = 35; //cell width
	    $w = 0;
	    
	    // Header
			foreach( $aForm['fields'] as $fld => $fld_value )
				{
					if( layout_view_field_table($fld_value) )
						{
							$this->Cell($cellw,7,$fld,1,0,'C',true);
							$w+=$cellw;
						}
				}
			$this->Ln();

	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('');
	    
	    // Data
	    $fill = false;
			foreach( $data as $record )
				{
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if( layout_view_field_table($fld_value) )
								{
									if(isset($record->$fld))
										{
											$value=$record->$fld; //dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
										}
									else
										{//TODO: add a comma separated list if widget==multi
											$value.= "&nbsp;";							
										}
									$this->Cell($cellw,6,$value,'LR',0,'L',$fill);
								}
						}
					$this->Ln();
					$fill=!$fill;
				}
			$this->Cell($w,0,'','T');
	}
}
?>