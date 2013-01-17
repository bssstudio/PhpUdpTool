
function getTimeFormat(from,to)
{
	//alert(from+" "+to);
	
	var diff = to - from;
	var timeformat;
	
	if (diff > 1000*60*60*24*150)
	{
		timeformat = "%0d. %0m. %y";
	}
	else if (diff > 1000*60*60*12)
	{
		timeformat = "%0d. %0m. %H:%M";
	}
	else if (diff > 1000*60*30)
	{
		timeformat = "%H:%M";
	}
	else if (diff > 1000*60*10)
	{
		timeformat = "%H:%M";
	}
	else
	{
		timeformat = "%H:%M:%S";
	}
	
	return timeformat;
}


var procTimeColor = "rgba(113,201,91,0.64)";

var ipdv_pos_r_data = [];
var ipdv_neg_r_data = [];
var ipdv_acu_r_data = [];
var proc_t_r_data = [];

var d = [];

var numOfDataPoints; // = 16000;
var dataSegmentation; // = 1000;

function agregateData_full(from,to)
{
	d = [ 
         {label:"IPDV_rolling+ [ms]", data:ipdv_pos_r},
           {label:"IPDV_rolling- [ms]", data:ipdv_neg_r},
           {label:"IPDV_rolling_accu [ms]", data:ipdv_acu_r},
           {label:"Processing time [ms]", data:proc_t_r}
      ];
}

function agregateData(from,to)
{
	//$('body').append("------------------------------------------<br >");
	
	yMax = 0;
	yMin = 0;
	
	numOfDataPoints = $('#res').val();
	
	var numOfOrigDataPoints = Math.round((to-from)/dataSegmentation);	
	var dppp = numOfOrigDataPoints / numOfDataPoints;
	
	if (numOfOrigDataPoints <= numOfDataPoints)
	{
		//alert("Unagregated");
		
		var fromFirstDataPoint = Math.round(from/dataSegmentation) * dataSegmentation;
		var toLastDataPoint = Math.round(to/dataSegmentation) * dataSegmentation;
		
		for (var j=fromFirstDataPoint; j<=toLastDataPoint; j+=dataSegmentation)
		{
			var index = Math.round((j - ipdv_acu_r[0][0])/dataSegmentation);
			
			
			
			if (typeof ipdv_acu_r[index] != 'undefined')
			{
				
				//$('body').append(j +" " + index+" "+ipdv_pos_r[index][0] +" "+ipdv_pos_r[index][1]+"<br >");
				
				if (ipdv_pos_r[index][1] > yMax)
				{
					yMax = ipdv_pos_r[index][1];
				}
				
				if (ipdv_acu_r[index][1] > yMax)
				{
					yMax = ipdv_acu_r[index][1];
				}
				
				if (ipdv_neg_r[index][1] < yMin)
				{
					yMin = ipdv_neg_r[index][1];
				}
				
				if (ipdv_acu_r[index][1] < yMin)
				{
					yMin = ipdv_acu_r[index][1];
				}
			}
			
		}
		
		d = [ 
	         {label:"IPDV_rolling+ [ms]", data:ipdv_pos_r},
	           {label:"IPDV_rolling- [ms]", data:ipdv_neg_r},
	           {label:"IPDV_rolling_accu [ms]", data:ipdv_acu_r},
	           {label:"Processing time [ms]", data:proc_t_r, color: procTimeColor}
	      ];
	}
	else
	{
		ipdv_pos_r_data = [];
		ipdv_neg_r_data = [];
		ipdv_acu_r_data = [];
		proc_t_r_data = [];
		
		for (var i=0; i<numOfDataPoints; i++)
		{
			var dataStart = Math.round( (from + (i * dppp * dataSegmentation)) / dataSegmentation) * dataSegmentation;
			var dataEnd = Math.round(  (from + ((i+1) * dppp * dataSegmentation)) / dataSegmentation  ) * dataSegmentation;
			
			
			var sum_ipdv_pos_r = 0;
			var sum_ipdv_neg_r = 0;
			var sum_ipdv_acu_r = 0;
			var sum_proc_t_r = 0;
			var count = 0;
			
			for (var dataI = dataStart; dataI <= dataEnd; dataI += dataSegmentation)
			{
				var index = Math.round((dataI - ipdv_acu_r[0][0])/dataSegmentation); 
				
				//alert(dataI +" " + index);
				
				if (typeof ipdv_acu_r[index] != 'undefined')
				{
				
				
							
					sum_ipdv_pos_r = sum_ipdv_pos_r + ipdv_pos_r[index][1];
					sum_ipdv_neg_r = sum_ipdv_neg_r + ipdv_neg_r[index][1];
					sum_ipdv_acu_r = sum_ipdv_acu_r + ipdv_acu_r[index][1];
					sum_proc_t_r = sum_proc_t_r + proc_t_r[index][1];
					
					count++;
				}
			}
			
			ipdv_pos_r_data.push([dataStart, sum_ipdv_pos_r / count] );
			ipdv_neg_r_data.push([dataStart, sum_ipdv_neg_r / count] );
			ipdv_acu_r_data.push([dataStart, sum_ipdv_acu_r / count] );
			proc_t_r_data.push([dataStart, sum_proc_t_r / count] );
			
			
			if (sum_ipdv_pos_r / count > yMax)
			{
				yMax = sum_ipdv_pos_r / count;
				//alert("max: "+yMax);
			}
			
			if (sum_ipdv_acu_r / count > yMax)
			{
				yMax = sum_ipdv_acu_r / count;
				//alert("max: "+yMax);
			}
			
			if (sum_ipdv_neg_r / count < yMin)
			{
				yMin = sum_ipdv_neg_r / count;
				//alert("min: "+yMin);
			}
			
			if (sum_ipdv_acu_r / count < yMin)
			{
				yMin = sum_ipdv_acu_r / count;
				//alert("min: "+yMin);
			}
			
			
		}
		
		
		d = [ 
	         {label:"IPDV_rolling+ [ms]", data:ipdv_pos_r_data},
	           {label:"IPDV_rolling- [ms]", data:ipdv_neg_r_data},
	           {label:"IPDV_rolling_accu [ms]", data:ipdv_acu_r_data},
	           {label:"Processing time [ms]", data:proc_t_r_data, color: procTimeColor}
	      ];
	
	}
	
	yMax *= 1.2;
	yMin *= 1.2;
	
	//alert(yMin+" - "+yMax);
	
	
}



var plot;
var options;
var timeformat;

var globalFrom;
var globalTo;

var yMax = 0;
var yMin = 0;

function displayGraph()
{

	//var options;
	globalFrom = ipdv_acu_r[0][0];
	globalTo = ipdv_acu_r[ ipdv_acu_r.length - 1 ][0];
	
	timeformat = getTimeFormat(ipdv_acu_r[0][0], ipdv_acu_r[ ipdv_acu_r.length - 1 ][0]);
	agregateData(ipdv_acu_r[0][0], ipdv_acu_r[ ipdv_acu_r.length - 1 ][0]);
	
	
	options = {
		series: {
			shadowSize: 0,
			stack: false,
			lines: {lineWidth: 1.5,  show: true, fill: false, steps: false },
			bars: { show: false, barWidth: 1 }
        },
		yaxis: { min: yMin, max: yMax },
		xaxis: { show: true, mode: "time",  timeformat: timeformat },
		legend: { position: 'nw', backgroundColor: "#fff" },
		grid: {backgroundColor: "#fff", markings: markings},
		selection: { mode: "x" }
	};


	

	//var plot;
	
	
	/*var d = [ 
           {label:"IPDV_rolling+ [ms]", data:ipdv_pos_r},
             {label:"IPDV_rolling- [ms]", data:ipdv_neg_r},
             {label:"IPDV_rolling_accu [ms]", data:ipdv_acu_r},
             {label:"Processing time [ms]", data:proc_t_r}
        ];*/
	
	/*d = [ 
	           {label:"IPDV_rolling+ [ms]", data:ipdv_pos_r_data},
	             {label:"IPDV_rolling- [ms]", data:ipdv_neg_r_data},
	             {label:"IPDV_rolling_accu [ms]", data:ipdv_acu_r_data},
	             {label:"Processing time [ms]", data:proc_t_r_data}
	        ];*/
	
	
	plot = $.plot($("#graph"), d, options);
	
	
	var overview = $.plot($("#overview"), d, {
        series: {
            lines: { show: true, lineWidth: 1 },
            shadowSize: 0
        },
        legend: {show: false},
        xaxis: { ticks: [], mode: "time" },
        yaxis: { ticks: [], autoscaleMargin: 0.1 },
        grid: {markings: markings },
        selection: { mode: "x" }
    });
	
	
	
	$("#graph").bind("plotselected", function (event, ranges) 
	{
		//alert("test");
		$("#busy1_holder").show();
		$("#busy1").activity();
		
		
		var from = Math.round(ranges.xaxis.from);
		var to = Math.round(ranges.xaxis.to);
		
		globalFrom = from;
		globalTo = to;
		
		timeformat = getTimeFormat(from, to);
		agregateData(from, to);
		
		// do the zooming
		
		setTimeout(function() {
			plot = $.plot($("#graph"), d,
                      $.extend(true, {}, options, {
                    	  yaxis: { min: yMin, max: yMax },
                          xaxis: { min: from, max: to, timeformat: timeformat }
                      }));
			
			$("#busy1_holder").hide();
			$("#busy1").activity(false);
			
			}, 100);
		
        

        // don't fire event on the overview to prevent eternal loop
        overview.setSelection(ranges, true);
        
        
        
    });
    
    $("#overview").bind("plotselected", function (event, ranges) {
        plot.setSelection(ranges);
    });
	
	
	
	return plot;
	
		
}


function redrawGraph()
{
	$("#busy1_holder").show();
	$("#busy1").activity();
	
	
	var from = globalFrom;
	var to = globalTo;
	
	timeformat = getTimeFormat(from, to);
	agregateData(from, to);
	
	// do the zooming
	
	setTimeout(function() {
		plot = $.plot($("#graph"), d,
                  $.extend(true, {}, options, {
                	  yaxis: { min: yMin, max: yMax },
                      xaxis: { min: from, max: to, timeformat: timeformat }
                  }));
		
		$("#busy1_holder").hide();
		$("#busy1").activity(false);
		
		}, 100);
}
