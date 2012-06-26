<?php

class Admin extends Controller {

	var $rights;
	
	function Admin()
	{
		parent::Controller();	
		$this->rights = 1;
	}
	
	
	function user($action = 'log')
	{
		$data['rights'] = 1;
		$data['login'] = '';
		
		if ($action == 'new')
		{
			$this->form_validation->set_rules('login', 'Utilisateur', 'required');
    			$this->form_validation->set_rules('password', 'Mot de Passe', 'required|matches[confirm]');
    			$this->form_validation->set_rules('confirm', 'Confirmation');
    			
    			if ($this->form_validation->run()) $this->simplelogin->create($this->input->post('login'), $this->input->post('password'), false);
		}
		
		//liste des utilisateurs
		$data['user_list'] = $this->db->get($this->simplelogin->user_table)->result();
		
		$data['title'] = "Administration Utilisateur";
		$this->viewlib->view('admin/user',$data,false);
	}
}
