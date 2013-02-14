<?php
$time1 = (microtime(true)*1000);
$time2 = 0;
$jitter = 0;
$sleepAdj = 0;
while (true) {

  $time3 = (microtime(true)*1000);
  usleep(20000 - round($sleepAdj*1000));
  
  //some hard stuf to do
  usleep(8000);
  
  $time4 = (microtime(true)*1000);
  
  $sleepTime = ($time4 - $time3);
  
  $sleepAdj = 0.99*$sleepAdj + 0.01*($sleepAdj + ($sleepTime-20));
  
  
  

  echo $sleepTime."        ".$sleepAdj."\n";
}
