<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('crudentry_model.php');


class Alerte_model extends Crudentry_model
{
	private $KidAlerte;
	private $idSite;
	private $date;
	private $etat;
	public $flux; //eau ou elec
	public $type;
	public $validite;
	
	function __construct($idAlerte=null, $type=null, $etat=null, $idAlerteParent=null, $info=null, $idSite=null, $Duree_validite=null, $flux=null)
	{
		parent::__construct();
		
		$this->type = $type;
		$this->flux = $flux;
		$this->validite = $Duree_validite;
		
		$date = date('Y-m-d');	
		$this->init('Alerte');
		
		$this->KidAlerte = null;
		if (!is_null($idAlerte)) $this->get($idAlerte);
		elseif ((!is_null($type)) and (!is_null($info))) $this->redalert($info, $idSite, $date);
		elseif ((!is_null($type)) and (!is_null($idAlerteParent)) and (($this->input->post('CommentaireCache')!='')))
		{
			if ($etat==2) $this->orangealert($idAlerteParent, $idSite, $date);
			elseif($etat==3) $this->greenalert($idAlerteParent, $idSite, $date);
		}
	}
	
	function get($idAlerte)
	{		
		$this->load($idAlerte);
		if ($this->editing()) $this->edit();
			
		$this->RW->Read = 1; 
		$this->RW->Insert = 1;
		$this->RW->Update = 1;

		$this->db->select('idAlerte');
		$this->db->where('idAlerteParent',$idAlerte);
		$query = $this->db->get('Alerte');
		if ($query->num_rows() > 0) 
		{
			$result = $query->result_array();
			$this->KidAlerte = new Alerte_model($result[0]['idAlerte']);
		}
		
		$this->make_inputs();
	}
	
	function edit()
	{
		$this->editing(true);
		$this->make_inputs();
	}
	
	function make_inputs()
	{
		$this->form['idAlerte']->set('idAlerte','hidden');
		$this->form['Flux']->set('Flux','hidden');
		$this->form['idSite']->set('idSite','hidden');
		$this->form['Date']->set('Date','date');
		$this->form['Commentaire']->set('Commentaire','textarea',2,100)->add_rule('required');
		$this->form['Etat']->set('Etat','flag',2);
		$this->form['idAlerteType']->set('Type d\'alerte','id_select');
		
		$this->add_form('CommentaireCache')->set('CommentaireCache','hidden');
		
			//DEFAULT VALUES :
			$this->setdata('Date',date('Y-m-d'));
			
			//DISPLAY PARAMETERS
			$this->fields_method('display');
			$this->fields_method('field','idAlerte');
			$this->fields_method('field','Flux');
			$this->fields_method('field','CommentaireCache');
			
			if ($this->editing()) 
			{
				$this->add_form('SubmitBTN')->set('Enregistrer','submit','save_alert')->field();
				$this->fields_method('field','Commentaire');
			}
			else
			{
				if ($this->getdata('Etat')==1)//rouge
				{
					if (is_null($this->KidAlerte))
					{
						$this->add_form('OrangeBTN')->set('flag_orange','submitIMG','new_orange_alert','Commentaire justifiant le changement du niveau d alerte :','CommentaireCache')->field();	
						$this->add_form('GreenBTN')->set('flag_green','submitIMG','new_green_alert','Commentaire justifiant le changement du niveau d alerte :','CommentaireCache')->field();
					}
				}
				elseif ($this->getdata('Etat')==2)//orange
				{
					$this->add_form('EditBTN')->set('pencil','submitIMG','edit_alert')->field();
					if (is_null($this->KidAlerte))
					{	
						$this->add_form('GreenBTN')->set('flag_green','submitIMG','new_green_alert','Commentaire :','CommentaireCache')->field();
					}
				}
				elseif($this->getdata('Etat')==3)//vert
				{
					$this->add_form('EditBTN')->set('pencil','submitIMG','edit_alert')->field();
				}
			}
	}
	
	function redalert($info, $idSite, $date)
	{
		$this->setdata('Etat', 1); //alerte rouge
		$this->setdata('idAlerteType',$this->type);
		$this->setdata('Commentaire',$info);
		$this->setdata('idSite',$idSite);
		$this->setdata('Date',$date);
		$this->setdata('Flux',$this->flux);
		//$this->save();
	}
	
	function orangealert($idAlerteParent, $idSite, $date)
	{
		$this->setdata('Etat', 2); //alerte orange
		$this->setdata('idAlerteType',$this->type);
		$this->setdata('idAlerteParent',$idAlerteParent);
		$this->setdata('idSite',$idSite);
		$this->setdata('Date',$date);
		$this->setdata('Flux',$this->flux);
		$this->setdata('Commentaire', $this->input->post('CommentaireCache'));
		$this->save();
	}
	
	function greenalert($idAlerteParent, $idSite, $date)
	{
		$this->setdata('Etat', 3); //alerte verte
		$this->setdata('idAlerteType',$this->type);
		$this->setdata('idAlerteParent',$idAlerteParent);
		$this->setdata('idSite',$idSite);
		$this->setdata('Date',$date);
		$this->setdata('Flux',$this->flux);
		$this->setdata('Commentaire', $this->input->post('CommentaireCache'));
		$this->save();
		//redirect(uri_string());
	}
	
	function process($idTypeAlerte, $idSite, $flux)
	{
		switch ($this->action)
		{
			case 'save_alert':
				if ($this->checkfields())
				{
					$this->save();
					redirect(uri_string());
				}
			break;
			
			case 'edit_alert':
				$this->edit();
			break;
			
			case 'new_orange_alert':
				$this->etat = 0;
				$this->KidAlerte = new Alerte_model(null,$idTypeAlerte,2,$this->id(),null,$idSite,'',$flux);
				//$this->KidAlerte->edit();
				redirect(uri_string());
			break;

			case 'new_green_alert':
				$this->etat = 0;
				$this->KidAlerte = new Alerte_model(null,$idTypeAlerte,3,$this->id(),null,$idSite,'',$flux);
				//$this->KidAlerte->edit();
				redirect(uri_string());
			break;
		}
	}
}
?>
