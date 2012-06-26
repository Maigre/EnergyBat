<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gwikid_model extends Model
{
	private $timer;
	
	function __construct()
	{
		parent::Model();
		$this->timer = $this->getTime();
	}
	
	//HELPERS FUNCTION, TODO : PUT THAT INTO A HELPER DAMN IT !
	function getEntry($table,$where,$multi=false)
	{
		$query = $this->db->query("SELECT * FROM `".$table."` WHERE ".$where);
		
		if (!$multi) return $query->row_array();
		else return $query->result_array();
	}
	
	function idTOfield($table,$id,$field='Nom')
	{
		$this->db->where('id'.$table, $id);
		$this->db->limit(1);
		$query = $this->db->get($table);
		
		if ($res = $query->row_array()) 
		{
			if ($field == 'Nom')
			{
				if (isset($res['Nom'])) return $res['Nom'];
				else if (isset($res['Name'])) return $res['Name'];
			}
			else if (($field != '')&&(isset($res[$field]))) return $res[$field];
		}
		
		return null;
	}
	
	//DATE
	function dateSTAMP($date,$type='/')
	{
		if ($type == '-') return strtotime($date);
		else if ($type == '/') return strtotime($this->dateSQL($date));
		return 0;
	}
	
	function dateSQL($date)
	{
		list($d,$m,$y) = explode('/',$date);
		if (strtotime($y.'-'.$m.'-'.$d) > 0) return date('Y-m-d',strtotime($y.'-'.$m.'-'.$d));
	}
	
	function dateDISP($date)
	{
		if (count(explode('-',$date)) < 3) return date('d/m/Y',$date);
		return date('d/m/Y',strtotime($date));
	}
	
	//FORMAT NUMBERS & STRINGS
	function num($string)
	{
		$out = null;
		foreach(str_split($string) as $s)
		{
			if (is_numeric($s)) $out .= $s;
			else if (($s == '.')||($s == ',')) $out .= '.';
		}
		
		return (float)$out;
	}
	
	//BENCHMARK Timer
	function timer($disp = false)
	{
		$tim = $this->getTime();
		if ($disp) echo '<strong>+'.($tim-$this->timer).' s</strong><br />';
		$this->timer = $tim;
	}
	
	function getTime() 
	{
		$timer = explode( ' ', microtime() );
		$timer = $timer[1] + $timer[0];
		return $timer;
	}

}