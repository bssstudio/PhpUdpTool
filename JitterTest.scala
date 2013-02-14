

object JitterTest {
  
  def main(args: Array[String]) {
    
    
    def nanoToMilliNano(nanoIn: Long) = {
      
      val milli = nanoIn / 1000000;
      val nano = nanoIn % 1000000;
      (milli.toInt, nano.toInt)
    }
    
    
    def doWork(sleepAdj: Long): Unit = {
      
      val t1 = System.nanoTime()   
      
      val (milli, nano) = nanoToMilliNano(28000000 - sleepAdj)
      Thread.sleep(milli, nano)
      
      val t2 = System.nanoTime()
      
      
      val diff21 = t2 - t1
      
      val sleepAdjNew = (0.99*sleepAdj + 0.01*(sleepAdj + (diff21-20000000))).toLong;
      
      
      println(diff21 + "      "+ sleepAdj+"     "+sleepAdjNew);
      
      
      doWork(sleepAdjNew)
    }
    
    
    doWork(0)
    
  }
}
