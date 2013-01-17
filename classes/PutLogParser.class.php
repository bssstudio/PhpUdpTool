<?php 

class PutLogParser
{
	private $stats;
	private $losses;
	private $info;
	
	private $start_time;
	private $end_time;
	
	function setTimeLimit($type, $time)
	{
		if (!is_int($time))
		{
			$time = str_replace("_", " ", $time);
			$time = strtotime($time);
		}
		
		if ($type == 'start')
		{
			$this->start_time = $time;
		}
		else if ($type == 'end')
		{
			$this->end_time = $time;
		}
		
		
	}
	
	
	
	
	function parseLog($filename, $live=true)
	{
		$parsed_array = array();
		
		if (file_exists($filename))
		{
			if ($live)
			{
				$random_str = rand(10000, 99999);
				
				//linux only
				exec("cp '".$filename."' /tmp/put_parser_tmp".$random_str);
				$filename = '/tmp/put_parser_tmp'.$random_str;
				
			}

		
			$handle = @fopen($filename, "r");
			if ($handle) 
			{
				//read every line
				while (($buffer = fgets($handle, 4096)) !== false) 
				{
					$line = str_replace(array(" \r"," \n"), "", $buffer);
					$line = str_replace(array("\r","\n"), "", $line);
					
					$line_array = explode(" ",$line);
					
					
					
					
					
					
					
					switch ($line_array[0]) 
					{
						case 'stats:':
							
							if (
								($this->start_time == null || strtotime(str_replace("_"," ",$line_array[1])) >= $this->start_time )
								&& ($this->end_time == null || strtotime(str_replace("_"," ",$line_array[1])) <= $this->end_time )
							)
							{
								$this->stats[$line_array[1]] = new PutStat($line_array);
							}
							
							
							
							
						break;
						
						case 'info:':
							if ($this->info == null)
							{
								$this->info = new PutInfo();
							}
							
							$this->info->proccessInfoLine($line_array);
							
						break;
						
						case 'loss:':
							
							if (
								($this->start_time == null || strtotime(str_replace("_"," ",$line_array[1])) >= $this->start_time )
								&& ($this->end_time == null || strtotime(str_replace("_"," ",$line_array[1])) <= $this->end_time )
							)
							{
								$this->losses[$line_array[1]."_".$line_array[2]] = new PutLoss($line_array);
							}
							
						break;
						
					}
					
					
					
				}
				
				if (!feof($handle))
				{
					echo "Error: unexpected fgets() fail\n";
				}
				
				fclose($handle);
				
				
				if ($live)
				{
					exec("rm ".$filename);
				}
				
				
				$parsed_array = array("stats" => $this->stats,"losses" => $this->losses, 'info' => $this->info);
				
			}
			
		}
		
		return $parsed_array;
	}
	
	
	
}