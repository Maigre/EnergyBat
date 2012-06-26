<?php

class Charts
{
	private $data;
	private $current_serie;
	
	function __construct($title,$width,$height,$abscisse='',$legend = false,$ordonnee='')
	{
		$this->data['title'] = $title;
		$this->data['width'] = $width;
		$this->data['height'] = $height;
		$this->data['abscisse'] = $abscisse;
		$this->data['xunit'] = $abscisse;
		$this->data['yunit'] = $ordonnee;
		$this->data['legend'] = $legend;
	}
	
	//SCATTER
	function serie($serie,$size=null,$color=null)
	{
		$this->current_serie = $serie;
		if ($size) $this->data['data'][$serie]['size'] = $size;
		if ($color) $this->data['data'][$serie]['color'] = $color; else $this->data['data'][$serie]['color'] = 'orange';
		if (!isset($this->data['data'][$serie]['liste'])) $this->data['data'][$serie]['liste'] = array();	
	}

	function point($x,$y,$label='',$id=null)
	{
		$this->data['data'][$this->current_serie]['liste'][] = array('x' => $x, 'y' => $y, 'label' => $label, 'id' => $id);
	}
	
	//COMMON
	function Graph($type)
	{		
		$datas = $this->data; 
		$datas['height'] *= 2;
		$datas['width'] *= 2;
		
		$dim = ' width="'.($this->data['width']).'"';
		
		return '<img src="'.base_url().'external/'.$type.'Me.php?values='.urlencode(json_encode($datas)).'" '.$dim.' height="auto">';
	}
	
	//SAVE IMAGE
	function GraphReal($type,$filename,$size='Disp')
	{
		$datas = $this->data; 
		$datas['height'] *= 2;
		$datas['width'] *= 2;

		$datas['filename'] = './output/graph/'.$filename.'.png';
		
		$page = base_url()."external/".$type."Me.php?values=".urlencode(json_encode($datas));	
		
		if (ini_get('allow_url_fopen')) 
		{
			$str = file_get_contents($page);
		}
		else if (function_exists('curl_init'))
		{
			$ch = curl_init();
			$timeout = 5; // set to zero for no timeout
			curl_setopt ($ch, CURLOPT_URL, $page);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$str = curl_exec($ch);
			curl_close($ch);
		}

		file_put_contents($datas['filename'], $str);
		
		if ($size == 'Print') $dim = ' width="'.(2*$this->data['width']).'"';
		else $dim = ' width="'.($this->data['width']).'"';
		return '<img src="'.base_url().'output/graph/'.$filename.'.png" '.$dim.' height="auto">';	
	}
	
	function ScatterJS()
	{
		include_once ('external/Flot/Scatter.php');		
		return scatterJS($this->data);	
	}
}
?>
