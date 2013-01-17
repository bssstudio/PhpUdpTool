<?php 

class PutInfo
{
	public $packet_segmentation;
	public $pps;
	public $dataSegmentation = 1000;
	
	public function proccessInfoLine($line_array)
	{
		switch ($line_array[1])
		{
			case 'packet_segmentation':
				$this->packet_segmentation = $line_array[2];
				break;
				
			case 'pps':
				$this->pps = $line_array[2];
				break;
		}
	}
	
}