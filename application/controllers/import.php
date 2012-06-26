<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import extends Controller {
 

 
    function __construct() 
    {
        parent::__construct();

    }


 	
	function index($entries_added=-1) 
    {
    	$data['title'] = "Import Données Conso";
    	$data['error'] = $this->input->post('error');
    	$data['success'] = '';
    	
    	if ($entries_added >= 0) $data['success'] = $entries_added." entrées ajoutées avec succès !";
    	
    	
    	$query = $this->db->get('upload');
			
	if (($query->num_rows() > 0))
	{	
		foreach ($query->result() as $row) 
		{
			//formatage de la date
			$date_array = explode("-",$row->date_creation); // split the array
			$var_year = $date_array[0]; //day seqment
			$var_month = $date_array[1]; //month segment
			$var_day = $date_array[2]; //year segment
			$row->date_creation = "$date_array[2]/$date_array[1]/$date_array[0]";
			
			$data['uploads'][]=array('nom_fichier'=>$row->nom_fichier,
								'date_creation'=>$row->date_creation);
		}
	}
    	$this->viewlib->view('import/upload_form',$data);
    }   	   	
    	
		
		
}
