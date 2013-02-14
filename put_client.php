<?php

$packet_segmentation = 20 *1000; //microSeconds
$pps = 1000000 / $packet_segmentation;

// set some variables
$host = $argv[1];
$port = $argv[2];

//packet settings
$packet_size = 250;

$settings = array();
//read settings
for ($i=2; $i<count($argv); $i++) {
	if (strrpos($argv[$i], "=") !== false) {
		// split the input into key value pair
		$tmppair = explode("=", $argv[$i]);
		$settings[$tmppair[0]] = $tmppair[1];	
	}	
}

//check the settings
foreach ($settings as $key => $value) {
	switch ($key) {
		case 'packet_segmentation':
			$packet_segmentation = intval($value);
			break;
			
		case 'packet_size':
			$tmp_packet_size = intval($value);
			
			if ($tmp_packet_size > 50 && $tmp_packet_size < 20000) {
				$packet_size = $tmp_packet_size;
			}
			
			break;
	}
}


function printStats()
{
	global $sent_ipdv_pos_rolling,$sent_ipdv_neg_rolling;
	
	echo "stats: ";
	
	$time = time();
	echo date("Y-m-d_H:i:s",$time)." ";
	
	echo "ipdv_p_r ".number_format($sent_ipdv_pos_rolling,3)." ";
	echo "ipdv_n_r ".number_format($sent_ipdv_neg_rolling,3)." ";
	echo "ipdv_a_r ".number_format($sent_ipdv_pos_rolling+$sent_ipdv_neg_rolling,3)." ";
	
	echo "\n";
}


// don't timeout!
set_time_limit(0);
// create socket
$socket = socket_create(AF_INET, SOCK_DGRAM, 0) or die("Could not create socket\n");
// bind socket to port
$result = socket_connect($socket, $host, $port) or die("Could not bind to socket\n");

socket_write($socket, "s:$packet_segmentation\n");


echo "debug: PUT client started.\n";
echo "debug: Packet size: ".$packet_size."\n";
echo "debug: Packet segmentation: ".($packet_segmentation/1000)." ms\n";
echo "debug: Packets per second: ".$pps."\n";


//stats
$last_send_microtime = intval(microtime(true)*1000);
$sent_ipdv_pos_rolling = 0;
$sent_ipdv_neg_rolling = 0;
$rolling_rate = 0.05;

//callibration
$sleepAdj = 0;

$i=0;
while (true)
{
	
	if($i % ($pps * 60* 30) == 1)
	{
		socket_write($socket, "s:$packet_segmentation\n");
	}
	
	
	$outstr = "p:".$i . ": ";
	
	for ($j=strlen($outstr); $j< $packet_size; $j++)
	{
		$outstr .=" ";
	}
	
	
	socket_write($socket, $outstr."\n");
	$send_microtime = microtime(true)*1000;
	
	
	$cur_ipdv = ($send_microtime - $last_send_microtime) - ($packet_segmentation/1000);
	
	if (abs($cur_ipdv) < 5*$packet_segmentation)
	{
		if ($cur_ipdv >= 0)
		{
			$sent_ipdv_pos_rolling = (1-$rolling_rate)*$sent_ipdv_pos_rolling + $rolling_rate * $cur_ipdv;
		}
		
		if ($cur_ipdv <= 0)
		{
			$sent_ipdv_neg_rolling = (1-$rolling_rate)*$sent_ipdv_neg_rolling + $rolling_rate * $cur_ipdv;
		}
	}
	
	
	
	if ($i % (5*$pps) == 0)
	{
		printStats();
	}
	
	
	$sleepAdj = 0.99*$sleepAdj + 0.01*($sleepAdj + ($cur_ipdv*1000));
	echo ($cur_ipdv*1000)."        ".$sleepAdj."\n";
	
	$last_send_microtime = $send_microtime;

	$i++;
	usleep($packet_segmentation-$sleepAdj);
	
}
