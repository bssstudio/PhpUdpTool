<?php

// set some variables
$host = $argv[1];
$port = intval($argv[2]);
// don't timeout!
set_time_limit(0);
// create socket
$socket = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");
// bind socket to port
$result = socket_bind($socket, $host, $port) or die("Could not bind to socket\n");
// start listening for connections
//$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");
// accept incoming connections
// spawn another socket to handle communication


//settings
$expected_packet_segmentation = 20; //ms
$expected_pps = 1000 / $expected_packet_segmentation; 

$stats_interval = 1; //seconds


function printPacketLoss($sequence_number, $sequence_diff, $report_type, $additional_array = array() )
{
	echo "loss: ";
	
	$time = time();
	echo date("Y-m-d_H:i:s",$time)." ";
	
	$miliseconds = round(microtime(true) * 1000) - ($time * 1000);
	
	echo $miliseconds." "; 
	
	echo $sequence_number." ";
	
	echo $sequence_diff." ";
	
	
	echo $report_type;
	
	if (is_array($additional_array) && count($additional_array) > 0)
	{
		foreach ($additional_array as $additional)
		{
			echo " ".$additional;
		}
		
	}
	
	echo "\n";
	
}


function printStats()
{
	global 	$packets_lost,
			$packets_out_of_order,
			$packets_received,
			$ipdv_pos_sum,
			$ipdv_neg_sum,
			$ipdv_pos_count,
			$ipdv_neg_count,
			$ipdv_max,
			$ipdv_min,
			$ipdv_pos_rolling,
			$ipdv_neg_rolling,
			$processing_time_rolling
	;
	
	
	$ipdv_pos_avg = $ipdv_pos_sum/$ipdv_pos_count;
	$ipdv_neg_avg = $ipdv_neg_sum/$ipdv_neg_count;
	
	echo "stats: ";
	
	$time = time();
	echo date("Y-m-d_H:i:s",$time)." ";

	echo "r ".$packets_received." ";
	echo "l ".$packets_lost." ";
	echo "o ".$packets_out_of_order." ";
	echo "ipdv_pos ".number_format($ipdv_pos_avg,3)." ";
	echo "ipdv_neg ".number_format($ipdv_neg_avg,3)." ";
	echo "ipdv_acu ".number_format($ipdv_pos_avg+$ipdv_neg_avg,3)." ";
	echo "ipdv_p_r ".number_format($ipdv_pos_rolling,3)." ";
	echo "ipdv_n_r ".number_format($ipdv_neg_rolling,3)." ";
	echo "ipdv_a_r ".number_format($ipdv_pos_rolling+$ipdv_neg_rolling,3)." ";
	echo "proc_t_r ".$processing_time_rolling." ";
	
	echo "\n";
}


//stats
$packets_lost = 0;
$packets_received = 0;
$packets_out_of_order = 0;
$last_packet_received_microtime = 0;
$ipdv_pos_sum = 0;
$ipdv_neg_sum = 0;
$ipdv_pos_count = 1;
$ipdv_neg_count = 1;
$ipdv_max = 0;
$ipdv_min = 0;

$ipdv_pos_rolling = 0;
$ipdv_neg_rolling = 0;
$rolling_rate = 0.05;

$processing_time_rolling = 0;

$last_packet_ok = false;

echo "PUT server started\n";

$sequence_num_before = -1;
$last_second_time = 0;

while (true)
{
	//$spawn = socket_accept($socket) or die("Could not accept incoming connection\n");
	// read client input
	while ($input = socket_read($socket, 1500) or die("Could not read input\n"))
	{
		$microtime_received = (microtime(true)*1000);
		
		
		// clean up input string
		
		$msg_len = strlen($input);
		
		$input = trim($input);
		
		//DEBUG//echo "raw: ".$input." + {$msg_len} Bytes \n";
		
		$input_array = explode(":", $input);
		
		//packet types
		if ($input_array[0] == 'p')
		{
			//packet
			$sequence_num = $input_array[1];
			$sequence_diff = $sequence_num - $sequence_num_before;
			
			
			if ($sequence_diff == 1)
			{
				$packets_received++;
				
				
				//OK!
				if ($sequence_num % $expected_pps == 0)
				{
					
					
					if ($last_second_time - (microtime(true)*1000) > 1100)
					{
						//echo "Packets late...(".$last_second_time - (microtime(true)*1000).")\n";
						printPacketLoss($sequence_num, $sequence_diff, 'late', array($last_second_time - (microtime(true)*1000)));
						
					}
					
					$last_second_time = microtime(true)*1000;
				}
				
				//last microtime first set
				if ($last_packet_received_microtime == 0)
				{
					$last_packet_received_microtime = $microtime_received;
				}
				else
				{
					//IPDV -- "jitter"
					$cur_ipdv = ($microtime_received - $last_packet_received_microtime)-$expected_packet_segmentation;
					
					if(abs($cur_ipdv)< 100*$expected_packet_segmentation && $last_packet_ok)
					{
						if ($cur_ipdv >= 0)
						{
							$ipdv_pos_sum += $cur_ipdv;
							$ipdv_pos_rolling = (1-$rolling_rate)*$ipdv_pos_rolling + $rolling_rate*$cur_ipdv;
							$ipdv_pos_count++;
						}
						else if ($cur_ipdv <= 0)
						{
							$ipdv_neg_sum += $cur_ipdv;
							$ipdv_neg_rolling = (1-$rolling_rate)*$ipdv_neg_rolling + $rolling_rate*$cur_ipdv;
							$ipdv_neg_count++;
						}
						
						
						if ($packets_received > 10)
						{
						
							//max
							if ($cur_ipdv > $ipdv_max)
							{
								$ipdv_max = $cur_ipdv;
							}
							
							//min
							if ($cur_ipdv < $ipdv_min)
							{
								$ipdv_min = $cur_ipdv;
							}
							
						}
						
					}
				}
				
				$sequence_num_before = $sequence_num;
				$last_packet_received_microtime = $microtime_received;
				$last_packet_ok = true;
				
				//stats
				if ($packets_received % ($expected_pps * $stats_interval) == 0)
				{
					printStats();
				}
				
			}
			else
			{
				if ($sequence_diff < 0)
				{
					printPacketLoss($sequence_num, $sequence_diff, 'out_of_order');
					
					$packets_out_of_order++;
					
					if ($sequence_diff < -100)
					{
						$sequence_num_before = $sequence_num;
					}
				}
				else if ($sequence_diff > 1)
				{
					printPacketLoss($sequence_num, $sequence_diff, 'loss');
					
					$packets_lost++;
					
					$sequence_num_before = $sequence_num;
				}
				
				$last_packet_ok = false;

			}
			
			
		}
		else if ($input_array[0] == 's')
		{
			//settings
			$expected_packet_segmentation = $input_array[1] / 1000;
			$expected_pps = 1000 / $expected_packet_segmentation;
			
			echo "info: packet_segmentation ".$expected_packet_segmentation."\n";
			echo "info: pps ".$expected_pps." ";
			echo "info: stats_interval ".$stats_interval."\n";
		}
		
		
		// reverse client input and send back
		//$output = strrev($input) . "\n";
		//socket_write($spawn, $output, strlen ($output)) or die("Could not write	output\n");
		
		
		$processing_time_rolling = $processing_time_rolling * (1-$rolling_rate) +  $rolling_rate * ((microtime(true)*1000) - $microtime_received);
		
	}
	
	// close sockets
	//socket_close($spawn);
}

socket_close($socket);

?>