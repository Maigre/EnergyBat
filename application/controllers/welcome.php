<?php

class Welcome extends Controller {

	var $info;
	
	function Welcome()
	{
		parent::Controller();	
		$this->info = '';
	}
	
	function index()
	{
		$data['title'] = "Bienvenue / Welcome ";
		
		if ($this->simplelogin->logged_in()) 
		{
			$data['user'] = $this->simplelogin->user();
			$this->viewlib->view('welcome_user',$data,true);
		}
		else 
		{
			$this->viewlib->view('welcome_login',$data,true);
		}
	}
	
	function login()
	{
		$this->form_validation->set_rules('login', 'Utilisateur', 'required');
    		$this->form_validation->set_rules('password', 'Mot de Passe', 'required');
    		
    		if ($this->form_validation->run())
    		{
    			if ($this->simplelogin->login($this->input->post('login'),$this->input->post('password'))) 
    			{
    				$this->session->set_userdata('info', 'Identification reussie. Bonjour '.$this->simplelogin->user().' !');
    				redirect('welcome','refresh');
    				//echo '<script>window.location = "'.site_url("welcome").'"</script>';
				}
				else $this->session->set_userdata('info', 'Utilisateur ou Mot de Passe incorrect');
			}
			
    		$this->index();
	}
	
	function logout()
	{
		$this->simplelogin->logout();
    		redirect('welcome','refresh');
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */
