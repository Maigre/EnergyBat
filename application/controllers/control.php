<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Control extends Controller {
 
    function __construct() 
    {
        parent::__construct();
    }
 
    function index() 
    {
    	
    }
    
    function ok() 
    {
    	$data['Nom'] = '1again';
    	$data['Prenom'] = 'J0nny';
    	
    	$data['title'] = "Control Page";
    	
    	$this->viewlib->view('ok',$data);
    }
    
    function Crud()
    {
    	$this->load->model('Crudentry_model','crud');
    	$this->crud->set('Crud',1);
    	$this->crud->data->Nom = 'Genial';
    	$this->crud->data->Date = date('Y-m-d');
    	$this->crud->save();
    	
    	if ($this->crud->valid()) echo 'ok';
    }
}
