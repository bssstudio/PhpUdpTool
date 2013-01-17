<?php 


class PutAgregatedLoss
{
	
	private $lost_packets_per_second;
	
	function __construct($loss_array,PutInfo $info)
	{
		
		//print_r($info);
		
		$this->lost_packets_per_second = array();
		
		if (is_array($loss_array))
		{
			
			foreach ($loss_array as $loss)
			{
				if ($loss->type == 'loss')
				{
					$first_add = 0;
					$full_time_loss = $loss->lost * $info->packet_segmentation;
					
					if ($loss->miliseconds < $full_time_loss)
					{
						
						$first_add = round($loss->lost *($loss->miliseconds / $full_time_loss));
						
					}
					else
					{
						$first_add = $loss->lost;
					}
					
					
					if (isset($this->lost_packets_per_second[$loss->time ]))
					{
						$this->lost_packets_per_second[$loss->time ] += $first_add;
					}
					else
					{
						$this->lost_packets_per_second[$loss->time ] = $first_add;
					}
					
					
					//echo "||".$loss->lost."|| |".$loss->datetime."|".($loss->time )."|0|$first_add| ";
					
					
					
					$loss->lost -= $first_add;
					
					
					$num_of_seconds = ceil($loss->lost / $info->pps);
					
					for ($i=1; $i <= $num_of_seconds; $i++)
					{
						
						$num_lost_to_add = $loss->lost;
					
						if ($num_lost_to_add > $info->pps)
						{
							$num_lost_to_add = $info->pps;
						}
						
						
						
						//echo "|".($loss->time - $i)."|$i|$num_lost_to_add| ";
						
						$loss->lost -= $num_lost_to_add;
						
						
						if (isset($this->lost_packets_per_second[$loss->time - $i]))
						{
							$this->lost_packets_per_second[$loss->time - $i] += $num_lost_to_add;
						}
						else
						{
							$this->lost_packets_per_second[$loss->time - $i] = $num_lost_to_add;
						}
					}
					
					//echo "<br />";
					
						
				}
			}
			
		}
		
		
	}
	
	function getAgrLossArray()
	{
		return $this->lost_packets_per_second;
	}
	
}