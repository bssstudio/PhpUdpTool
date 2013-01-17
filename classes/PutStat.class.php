<?php 

class PutStat
{
	public $datetime;
	public $time;

	
	public $stats_array;
	
	function __construct($log_line_array)
	{
		
		
		$this->datetime = $log_line_array[1];
		$this->datetime = str_replace("_", " ", $this->datetime);
		
		$this->time = strtotime($this->datetime);
		
		
		$key = '';
		$value = '';
		
		for ($i=2; $i<count($log_line_array); $i++)
		{
			if ($i % 2 == 0)
			{
				if ($key != '')
				{
					$this->stats_array[$key] = $value;
				}
				
				$key = $log_line_array[$i];
			}
			else
			{
				$value = $log_line_array[$i];
			}
		}
		
		//last data
		if ($key != '')
		{
			$this->stats_array[$key] = $value;
		}
		
		
	}
	
}