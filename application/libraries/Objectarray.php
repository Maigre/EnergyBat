<?php
class ObjectArray {
  
  public function fromValues($array,$att=false)
  {
  	$obj = new stdClass;
  	foreach($array as $value) 
  	{
  		if ($att != false) $obj->{$value->{$att}} = NULL;
  		else $obj->{$value} = NULL;
	}
  	return $obj;
  }
  
  public function full($array)
  {
  	$obj = new stdClass;
  	foreach($array as $key=>$value) $obj->{$key} = $value;
  	return $obj;
  }
}
?>