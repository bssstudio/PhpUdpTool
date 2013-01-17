<?php 


class PutLoss
{
	
	public $datetime;
	public $time;
	public $miliseconds;
	
	public $lost;
	
	public $type;
	
	public $stats_array;
	
	function __construct($log_line_array)
	{
		
		
		$this->datetime = $log_line_array[1];
		$this->datetime = str_replace("_", " ", $this->datetime);
		
		$this->time = strtotime($this->datetime);
		
		$this->miliseconds = $log_line_array[2];
		
		if ($this->miliseconds < 0)
		{
			$this->miliseconds = 0;
		}
		
		
		$this->type =  $log_line_array[5];
		
		if($this->type == 'loss')
		{
			$this->lost = $log_line_array[4] - 1;
		}
		else
		{
			$this->lost = 0;
		}
		
	}
	
	
	
	
}