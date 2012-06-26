<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Suivi extends Controller {
 
    function __construct() 
    {
        parent::__construct();
    }
 
    function index() 
    {
    	$data['title'] = "Control Page";
    	$this->viewlib->view('ok',$data);
    }
}
