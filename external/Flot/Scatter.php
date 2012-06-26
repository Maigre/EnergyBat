<?php

function scatterJS($array = null)
{
	ob_start();
		
	$color['orange'] = 0;
	$color['blue'] = 1;
	$color['red'] = 2;
	$color['green'] = 3;
	$color['purple'] = 4;
	$color['brown'] = 5;
	$color['grey'] = 6;
	
	global $GraphCounter;
	if (!$GraphCounter) $GraphCounter = 1;
	else $GraphCounter++;
	?>
	<table>
		<tr><td colspan="2" style="text-align:center"><strong><?php echo $array['title']?></strong></td></tr>
		<tr>
			<td><div style="-webkit-transform: rotate(-90deg);-moz-transform: rotate(-90deg);width:15px;"><?php echo $array['yunit']?></div></td>
			<td><div id="placeholder<?php echo $GraphCounter;?>" style="width:<?php echo ($array['width']-20)?>px;height:<?php echo ($array['height']-20)?>px"></div></td>
		</tr>
		<tr><td colspan="2" style="text-align:center"><?php echo $array['xunit']?></td></tr>
	</table>
	<style type="text/css">
    #placeholder<?php echo $GraphCounter;?> .button {
        position: absolute;
        cursor: pointer;
    }
    #placeholder<?php echo $GraphCounter;?> div.button {
        font-size: smaller;
        color: #999;
        background-color: #eee;
        padding: 2px;
    }
    </style>
	

	<script type="text/javascript">
	$(function () {

	    var data = [ 
	    		   	<?php
    				   	$i = 0;
    				   	//Series BE WARNED : no distinction between series, all data will be a different serie
    					foreach ($array['data'] as $name=>$datas)
    					{
    						//Datas
    						foreach ($datas['liste'] as $cc) 
    						{
    							if ($i > 0) echo ',';
    							echo '{ data: [['.$cc['x'].','.$cc['y'].']], name: "'.$cc['label'].'", id: '.$cc['id'].' }';
    							$i++;
    						}
    					}
    					
    					if (!isset($color[$datas['color']])) $datas['color'] = 'orange';
    					?>
    				];

	    var options = {
		        series: {
		           lines: { show: true },
		           points: { show: true },
		           color: <?php echo $color[$datas['color']];?>
		        },

		       	grid: { hoverable: true, clickable: true },
		       	zoom: {interactive: true, amount: 1.1},
				pan: {interactive: true, frameRate: 10}, 
				xaxis: {zoomRange: [0,null]},   // or [number, number] (min, max) or false
				yaxis: {zoomRange: [0,null]}
		     };
	     
	    var plot = $.plot($("#placeholder<?php echo $GraphCounter;?>"),data,options);

	    function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css( {
		    position: 'absolute',
		    display: 'none',
		    top: y + 5,
		    left: x + 5,
		    border: '1px solid #fdd',
		    padding: '2px',
		    'background-color': '#fee',
		    opacity: 0.80
		}).appendTo("body").fadeIn(100);
	    }

	  	plot.zoomOut();

	 	// Get the current zoom
	  	var zoom0 = plot.getAxes();

	 	// Add the zoom to standard options
	 	options.xaxis.min = zoom0.xaxis.min;
	 	options.xaxis.max = zoom0.xaxis.max;
	 	options.yaxis.min = zoom0.yaxis.min;
	 	options.yaxis.max = zoom0.yaxis.max;
	 	
	  	//reset zoom function
	  	function reset()
	  	{
	  		plot = $.plot($("#placeholder<?php echo $GraphCounter;?>"),data,options);
	  		plot.reset = reset;
	  		addReset();
	  	}
	  	plot.reset = reset;

	 	// add zoom out button 
	 	function addReset()
	 	{
		    $('<div class="button" style="right:20px;top:20px">reset zoom</div>').appendTo($("#placeholder<?php echo $GraphCounter;?>")).click(function (e) {
		        e.preventDefault();
		        plot.reset();
		    });
	 	}

	 	addReset();
	 	
	  	
	    var previousPoint = null;
	    $("#placeholder<?php echo $GraphCounter;?>").bind("plothover", function (event, pos, item) {
			if (true) {
			    if (item) {
			        //$("*").css("cursor", "pointer");
			        
			        if (previousPoint != item.dataIndex) {
			            //previousPoint = item.dataIndex;
			            
			            $("#tooltip").remove();
			            var x = item.datapoint[0].toFixed(2),
			                y = item.datapoint[1].toFixed(2);
			            
			            showTooltip(item.pageX, item.pageY, item.series.name + " : " + y + " <?php echo $array['yunit']?>");
			        }
			    }
			    else {
			        //$("*").css("cursor", "auto");
			        $("#tooltip").remove();
			        previousPoint = null;            
			    }
			}
	    });

	    $("#placeholder<?php echo $GraphCounter;?>").bind("plotclick", function (event, pos, item) {
		if (item) {
			window.location.href = "<?php echo site_url("consult/Site")?>/"+item.series.id;
			}
	    });
	});
	</script>

	<?php
	$ret = ob_get_contents();
	ob_end_clean();
	return $ret;
}

