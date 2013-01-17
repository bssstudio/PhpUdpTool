<?php

$start = null;
$end = null;


if (count($argv)>2)
{
	for ($i=2; $i<count($argv);$i++)
	{
		$arg_array = explode("=",$argv[$i]);
		
		switch ($arg_array[0])
		{
			case 'start':
				$start = $arg_array[1];
				break;
			
			case 'end':
				$end = $arg_array[1];
				break;
			
		}
		
	}
}


//html to export
$html = "<html><head>";

//load js libs

$html .='<script type="text/javascript">';
$html .= "\n\n";

$html .= file_get_contents("libjs/jquery-1.8.0.js");
$html .= "\n\n";

$html .= file_get_contents("libjs/jquery.flot.js");
$html .= "\n\n";

$html .= file_get_contents("libjs/jquery.flot.selection.js");
$html .= "\n\n";

$html .= file_get_contents("libjs/display_graph.js");
$html .= "\n\n";

$html .= file_get_contents("libjs/jquery.activity-indicator-1.0.0.js");
$html .= "\n\n";

$html .= 'numOfDataPoints = 5000;'."\n";


$html .='</script>';




$html .= "</head>";
$html .= "<body>";

$html .= 'Resolution: <select id="res" >'."\n";
for ($i=50; $i<= 128000; $i+=$i)
{
	$html .='<option value='.$i;
	
	if ($i == 3200)
	{
		$html .=' selected="selected" ';
	}
	
	$html .=' >'.$i.' points</option>'."\n";
}
$html .= '</select>'."\n";

$html .= '<div id="busy1_holder" style="z-index: 9999; position: fixed; top: 50%; left: 50%; margin-top: -50px; margin-left: -20px; " ><span id="percent">Loading...</span><div style="height: 50px;" id="busy1"></div></div>';
$html .= '<div id="graph" style="width: 100%; height:50% " ></div> <div id="overview" style="margin-left:50px;margin-top:20px;width:400px;height:50px"></div>';



require 'classes/PutLogParser.class.php';
require 'classes/PutStat.class.php';
require 'classes/PutLoss.class.php';
require 'classes/PutInfo.class.php';
require 'classes/PutAgregatedLoss.class.php';

$log_parser = new PutLogParser();

if ($start != null)
{
	$log_parser->setTimeLimit('start', $start);
}

if ($end != null)
{
	$log_parser->setTimeLimit('end', $end);
}

$stats_array = $log_parser->parseLog($argv[1]);



$html .= '<script type="text/javascript">';
$html .= "\n\n";
$html .= 'var ipdv_pos_r = []; '."\n";
$html .= 'var ipdv_neg_r = []; '."\n";
$html .= 'var ipdv_acu_r = []; '."\n";
$html .= 'var proc_t_r = []; '."\n";
$html .= 'var markings = [];'."\n";
$html .= 'dataSegmentation = '.$stats_array['info']->dataSegmentation.';'."\n";


$html .= 'function setDataValues() {'."\n";

if (isset($stats_array['stats']) && is_array($stats_array['stats']))
{
	//$stat = new PutStat(array());
	$stat_before = null;
	
	foreach ($stats_array['stats'] as $stat)
	{
		if ($stat_before != null)
		{
			$stat_time_diff = $stat->time - $stat_before->time;
			
			if ($stat_time_diff > 1)
			{
				for ($ii = $stat_before->time+1; $ii<$stat->time; $ii++)
				{
					
					if ($stat_time_diff > 10)
					{
						$html .= 'ipdv_pos_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.(0).' ]);'."\n";
						$html .= 'ipdv_neg_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.(0).' ]);'."\n";
						$html .= 'ipdv_acu_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.(0).' ]);'."\n";
						$html .= 'proc_t_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.(0).' ]);'."\n";
						
					}
					else
					{
					
						$html .= 'ipdv_pos_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.$stat_before->stats_array['ipdv_p_r'].' ]);'."\n";
						$html .= 'ipdv_neg_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.$stat_before->stats_array['ipdv_n_r'].' ]);'."\n";
						$html .= 'ipdv_acu_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.$stat_before->stats_array['ipdv_a_r'].' ]);'."\n";
						$html .= 'proc_t_r.push([ '.(strtotime(date("Y-m-d H:i:s",$ii)." UTC")*1000). ' , '.$stat_before->stats_array['proc_t_r'].' ]);'."\n";
					}
				}
			}
		}
		
		$html .= 'ipdv_pos_r.push([ '.(strtotime(date("Y-m-d H:i:s",$stat->time)." UTC")*1000). ' , '.$stat->stats_array['ipdv_p_r'].' ]);'."\n";
		$html .= 'ipdv_neg_r.push([ '.(strtotime(date("Y-m-d H:i:s",$stat->time)." UTC")*1000). ' , '.$stat->stats_array['ipdv_n_r'].' ]);'."\n";
		$html .= 'ipdv_acu_r.push([ '.(strtotime(date("Y-m-d H:i:s",$stat->time)." UTC")*1000). ' , '.$stat->stats_array['ipdv_a_r'].' ]);'."\n";
		$html .= 'proc_t_r.push([ '.(strtotime(date("Y-m-d H:i:s",$stat->time)." UTC")*1000). ' , '.$stat->stats_array['proc_t_r'].' ]);'."\n";
		
		$stat_before = $stat;
	}
}

$html .= 'markings = ['."\n";

if (isset($stats_array['losses']) && is_array($stats_array['losses']))
{
	
	
	
	
	//$stat = new PutStat(array());
	
	$agregated_loss = new PutAgregatedLoss($stats_array['losses'],$stats_array['info']);
	$agregated_loss_array = $agregated_loss->getAgrLossArray();
	
	//ksort($agregated_loss_array);
	
	
	$first_loss = true;
	
	//print_r($agregated_loss_array);
	
	foreach ($agregated_loss_array as $time => $loss_per_second)
	{
		//{ color: '#000', lineWidth: 1, xaxis: { from: 2, to: 2 } },
		
		if ($first_loss)
		{
			$first_loss = false;
		}
		else
		{
			$html .= ",";	
		}
		
		$loss_time = (strtotime(date("Y-m-d H:i:s",$time)." UTC")*1000);
		
		
		$loss_level = $loss_per_second / $stats_array['info']->pps;
		$color = '#000';
		$width = 1;
		
		
		
		
		if ($loss_level < 0.33)
		{
			$color = '#4de';//'#4de';
			$width = 0.5;
		}
		else if ($loss_level >= 0.33 && $loss_level < 0.66)
		{
			$color = '#333';
			$width = 1;
		}
		else
		{
			$color = '#e00';
			$width = 1.5;
		}
		
		
		$html .= "{ color: '".$color."', lineWidth: ".$width.", xaxis: { from: ".$loss_time.", to: ".($loss_time )." } }"."\n";
		
	}
	
	
}

$html .= ']; '."\n";


$html .= '}'."\n";


$html .= '$(document).ready(function() {'."\n";
$html .= "$('#busy1').activity();"."\n";
$html .= 'setDataValues();'."\n";
$html .= 'displayGraph();'."\n";
$html .= "$('#busy1_holder').hide();"."\n";
$html .= "$('#busy1').activity(false);"."\n";
$html .= "$('#res').change(function() { redrawGraph();  });"."\n";
$html .= '}); '."\n";



$html .= '</script>';




$html .= "</body>";
$html .= "</html>";


echo $html;