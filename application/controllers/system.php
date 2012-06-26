<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class System extends Controller {
 
    function __construct() 
    {
        parent::__construct();
    }
 
    function index() 
    {
    	$data['title'] = "Paramètres et Régalges";
    	$data['rights'] = 3;
    	
    	$this->viewlib->view('system/menu',$data);
    }
    
    function ministere()
    {
    	
    }
}
