<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
class Viewlib extends Controller{
 var $right_data;
 var $left_data;
 var $top_data;
 var $footer_data;
 var $data; 
 var $header;
 var $footer;
 var $left;
 var $right;
 var $top;
 var $theme;
 var $logged;
 var $auth;
 var $auto_redirect;
 
 
 function Viewlib()
 {
  $obj =& get_instance();
  
  $this->auto_redirect = false;  //renvoyer automatiquement vers la page de login si $this->auth est faux
  //$this->logged = $obj->ion_auth->logged_in();  //recupere l'etat logged ou non
  $this->logged = $obj->simplelogin->logged_in();  //recupere l'etat logged ou non
  $this->auth = false; //autorisation d'afficher le contenu
  
  //recupere le nom des vues dans la config 
  $this->header = $obj->config->item('header');
  $this->footer = $obj->config->item('bottom');
  $this->left = $obj->config->item('left');
  $this->right = $obj->config->item('right');
  $this->top = $obj->config->item('top');
  $this->theme = $obj->config->item('theme');
  $this->errorlogin = $obj->config->item('errorlogin');
  
  //data par defaut
  $this->data['info'] = '';
  $this->data['error'] = '';
 }
 
 function setData($data)
 {
   if (is_array($data))
   {
   	if (is_array($this->data)) $this->data = array_merge($this->data,$data);
   	else $this->data = $data;
   }
 }
 
 function setRight($data)
 {
  $this->right_data = $data;
 }
 
 function setLeft($data)
 {
  $this->left_data = $data;
 }
 
 function setTop($data)
 {
  $this->top_data = $data;
 }
 
 function setBottom($data)
 {
  $this->footer_data = $data;
 }
 function view($content, $datas = null, $force_auth = false, $templates = '', $return = false)
 {
    $obj =& get_instance();
    
    $this->setData($datas);
    
    $force_auth = true;
    if ($force_auth) $this->auth = true; //affichage de la page force (independant de logged ou autre)
    else if ($this->logged) 
    {
    	if (!isset($datas['rights'])) $this->auth = true; //affichage de la page si loggué et aucun droit specifié
    	else if ($datas['rights'] <= $obj->simplelogin->group()) $this->auth = true; //affiche si accreditation suffisante
    }
          
    $template = $obj->config->item('theme'); 
    
    //flash info
    if ($obj->session->userdata('info'))
    {
    	$page['info'] = $obj->session->userdata('info');
    	$obj->session->unset_userdata('info');
    }
    if ($obj->session->userdata('info_next'))
    {
	$obj->session->set_userdata('info', $obj->session->userdata('info_next'));
	$obj->session->unset_userdata('info_next');
    }
    //flash error
    if ($obj->session->userdata('error'))
    {
    	$page['error'] = $obj->session->userdata('error');
    	$obj->session->unset_userdata('error');
    }
    if ($obj->session->userdata('error_next'))
    {
	$obj->session->set_userdata('error', $obj->session->userdata('error_next'));
	$obj->session->unset_userdata('error_next');
    }
    
    //error template
    $obj->form_validation->set_error_delimiters('<div class="error">', '</div>');
 
    /*
    if($admins === true){
    $this->header = $obj->config->item('adminPageHeader');
    $this->footer = $obj->config->item('adminPageFooter');
    $this->left = $obj->config->item('adminLeftCol');
    $this->right = $obj->config->item('adminRightCol');
    $templates['master'] = $obj->config->item('adminMasterTemplate');
    }
    */

    $page['header'] = '';
    $page['top'] = '';
    $page['left'] = ''; 
    $page['right'] = '';
    $page['bottom'] = '';
    
    if(!isset($templates['header']) || $templates['header'] != false )
    {   
     if(isset($templates['header'])) $this->header =  $templates['header'];
     $page['header'] =  $obj->load->view($template.$this->header, $this->data, true);
    }
    
    if(!isset($templates['top']) || $templates['top'] != false )
    {   
     if(isset($templates['top'])) $this->top =  $templates['top'];
     
     //creating menu
     if ($obj->config->item('menu'))
     foreach ($obj->config->item('menu') as $link=>$title) 
     {
     	$current = $obj->uri->segment(1);
     	if (($current == $link)||(($current == '')&&($link == 'welcome'))) $id = 'id="current"';
     	else $id = '';
     	
     	$this->top_data['menu'][] = anchor($link,$title,$id);
     }
     
     
     $page['top'] =  $obj->load->view($template.$this->top, $this->top_data, true);
    }
    
    if(!isset($templates['left']) || $templates['left'] != false)
    {
     if(isset($templates['left'])) $this->left =  $templates['left'];
     $page['left'] =  $obj->load->view($template.$this->left, $this->left_data, true);
    }

    if(!isset($templates['right']) || $templates['right'] != false)
    {
     if(isset($templates['right'])) $this->right =  $templates['right'];
     $page['right'] =  $obj->load->view($template.$this->right, $this->right_data, true);
    }
    
    if(!isset($templates['bottom']) || $templates['bottom'] != false)
    {
    	if(isset($templates['bottom'])) $this->footer =  $templates['bottom'];
    	$this->footer_data['logged'] = $this->logged;
    	$page['bottom'] =  $obj->load->view($template.$this->footer, $this->footer_data, true);
    }
    
    if ($this->auth) $page['content'] =  $obj->load->view($content, $this->data, true);  //affiche la vue initialement demandée par le controleur
    else 
    {
    	$page['content'] = $obj->load->view($template.$this->errorlogin, $this->data, true); //message interdit si pas de redirection automatique
    	if ($this->auto_redirect) redirect('welcome', 'refresh'); //redirection automatique si acces interdit
    }

    
    $page['auth'] = $this->auth;
    
    
    if(isset($templates['master']) && $templates['master'] != false) $page_final = $obj->load->view($template.$templates['master'], $page,$return);
    else $page_final = $obj->load->view($template.$obj->config->item('masterTemplate'), $page,$return);
    
    if ($return) return $page_final;
 }
 
 function preview($content, $data){
  $obj =& get_instance();
    return $obj->load->view($this->theme.$content, $data, true);
 }   
 function alert($message)
 {
  echo "alert ('{$message}')</script>";
 }
 
 function alert_refresh($message, $refresh)
 {
  echo "alert ('{$message}')</script>";
  echo '<meta http-equiv="refresh" content="0;url='.site_url($refresh).'" />';
 }
}
?>  
