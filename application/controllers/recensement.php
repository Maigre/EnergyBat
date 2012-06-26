<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Recensement extends Controller {
 
    private $byPass1 = true; //sauter l'étape 1 du recensement
    
    function __construct() 
    {
        parent::__construct();
    }
 
    function index() //moteur de recherche des sites à recenser
    {
    	$data['title'] = "Recensement";
    	
    	$this->load->model('List_model','lister');

		$data['byPass1'] = $this->byPass1;
	
    	$data['virgins'] = $this->lister->get('Site',array('DateSaisie IS NULL' => null),array('Ministere','Equipe'),'DatePrevue');
    	$data['inprogress'] = $this->lister->get('Site',array('DateSaisie IS NOT NULL' => null,'ValideManu IS NULL' => null),array('Ministere','Equipe'),'DateSaisie');
    	$data['complete'] = $this->lister->get('Site',array('ValideManu' => 1),false);
    	
    	$data['count']['total'] = count($data['inprogress'])+count($data['complete']);
    	$data['count']['complete'] = count($data['complete']);
    	
    	$this->viewlib->view('recensement/browse',$data);
    }
    
    function site($idSite=0,$idBatiment=null) //formulaire de recensement
    {
	if (($idSite > 0)||($idSite === 'new'))
	{
		$data['title'] = "Recensement";
		$data['mode'] = $this->uri->segment(1);
		$data['byPass1'] = $this->byPass1; //sauter l'étape 1 du recensement
		
		$data['idSite'] = $idSite;    	
			
		//$this->load->model('Site_model','Site'); //LOAD SITE MODEL
		$this->load->model('Site_model','Site');
		$this->Site = new Site_model($this->Date_c_real);
		$this->Site->get($idSite,false,true,$this->byPass1); //LOAD SITE
	
		if ($this->Site->process()) $idBatiment = null; //EXECUTE SITE ACTIONS (save/edit/delete..)
		
		$data['Site'] = $this->Site->formfields();
		$data['Suivi'] = false;
		$data['Diagnostic'] = false;
		
		if (($this->Site->statut <= 1)&&(!$this->Site->byPass_statut1)) 
		{
			$this->viewlib->view('recensement/site_step1',$data);
			$data['Statut'] = '<div style="color:red">creation d\'un nouveau Site</div>';
		}
		else if ($this->Site->statut <= 2) 
		{
			$this->viewlib->view('recensement/site_step2',$data); 
			$data['Statut'] = '<div style="color:red">retour de visite</div>';
		}
		else if ($this->Site->statut >= 3) 
		{    		
			$data['Statut'] = '<div style="color:red">saisie du recensement en cours...</div>';
			
			$data['PLelecList']=null;
			if (isset($this->Site->PLelec))
			foreach ($this->Site->PLelec as $id=>$PL) $data['PLelecList'][$id] = $PL->formfields();
			
			$data['PLeauList']=null;
			if (isset($this->Site->PLeau))
			foreach ($this->Site->PLeau as $id=>$PL) $data['PLeauList'][$id] = $PL->formfields();
			
			$data['PLgasoilList']=null;
			if (isset($this->Site->PLgasoil))
			foreach ($this->Site->PLgasoil as $id=>$PL) $data['PLgasoilList'][$id] = $PL->formfields();
			
			$data['BatimentsList']=null;
			if (isset($this->Site->Batiments)) $data['BatimentsList'] = $this->Site->Batiments;
			
			$data['Site']['BATcount'] = count($data['BatimentsList'])-1;
			$data['Site']['PLcount'] = count($data['PLeauList'])+count($data['PLgasoilList'])+count($data['PLelecList'])-3;
			if ($data['Site']['PLcount'] == -2) $data['Site']['PLcount'] = 'modification';
			
			$data['idBatiment'] = $idBatiment;
			$data['Batiment']=null;
			if (($idBatiment > 0)||($idBatiment === 'new'))
			{				
				if ((!isset($this->Site->Batiments[$idBatiment]))&&($idBatiment !== 'new')) redirect('/recensement/site/'.$idSite.'/new', 'refresh');
					
				$this->load->model('Batiment_model','Batiment'); //LOAD BAT MODEL
				$this->Batiment->get($idBatiment); //LOAD BAT
				$this->Batiment->process($idSite);
				
				$data['Batiment'] = array_merge($this->Batiment->Bat_Occupation->formfields(),$this->Batiment->Bat_Technique->formfields(),$this->Batiment->Bat_Equip_Air->formfields());
				$data['Batiment'] = array_merge($data['Batiment'],$this->Batiment->formfields());
				
				for($i=1;$i<=$this->Batiment->data->NiveauNbr;$i++) 
				{
					$Niv = $this->Batiment->Bat_Niveaux[$i]->formfields();
					foreach($Niv as $key=>$input) $data['Batiment']['Niveaux'][str_replace('_'.$i,'',$key)][$i] = $input;
				}
				
				$data['Batiment']['Statut'] = $this->Batiment->statut;
			}
				
			$this->viewlib->view('site_display',$data);
		}
	}
	else redirect('/recensement/', 'refresh');
    }
}
