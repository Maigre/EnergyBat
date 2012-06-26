<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('crudentry_model.php');
include_once('histo_model.php');

//CLASSE COMMUNE A TOUS LES PL
class PL_model extends Crudentry_model
{
	private $type;
	private $histoType;
	private $histoSize;
	private $Date_c; //date courante
	public $histo;
	public $Puissance;
	
	function __construct($type,$idSite,$idPL=false,$Stat4=false,$histoSize=495,$Date_c)
	{
		parent::__construct();
		
		$this->type = $type;
		$this->histoType = null;
		$this->histoSize = $histoSize;
		
		$this->Date_c = $Date_c;
	
		
		$this->init('Site_PL_'.$type);
		$this->statut = 0;
		
		$this->setdata('idSite',$idSite);
		
		if ($idPL) $this->get($idPL,$Stat4);
	}
	
	function get($idPL,$forced_statut=false,$Stat4=false)
	{
		//fill data from DB
		$this->load($idPL);
		
		$this->statut = 1; //PL EXISTANT, A VALIDER, VERIFIER INFO : pas d'usage de l'histo
				//2 : modification d'un PL (usage de l'historique) -> via forced statut
		if ($this->data->Valide != null) $this->statut = 3; //PL VALIDE, DISPLAY
		if ($Stat4 == 4) $this->statut = 4; //CONSULT
		if ($forced_statut) $this->statut = $forced_statut; //STATUT FORCE (pour reedition))
		
		if ($this->statut == 2) $this->editing = true; //set editing mode ON
				

		if ($this->statut > 2) 
		{
			//check NoPL exist or retrieve it with NoCompteur
			if ($this->type == 'eau') $this->histoType = 'BT';
			else if ($this->type == 'elec') $this->histoType = $this->idTOfield('Tension',$this->data->idTension,'Type');
			
			if ($h = $this->histoType)
				if ((!is_numeric($this->data->NoPL))&&($this->data->NoCompteur != ''))
				{
					if($co = $this->getEntry('Conso_'.$h,"`N° compteur` LIKE '%".$this->data->NoCompteur."'"))
					{
						$this->setdata('NoPL',$co['Point_de_livraison']);
						$this->save(false);
					}					
				}
					
			//GET HISTO !
			if ($this->type == 'eau') 		$this->histo = new histoEAU_model($this->data->NoPL,$this->histoSize,$this->Date_c);
			else if ($this->histoType == 'BT') 	$this->histo = new histoBT_model($this->data->NoPL,$this->histoSize,$this->Date_c);
			else if ($this->histoType == 'MT') 	$this->histo = new histoMT_model($this->data->NoPL,$this->histoSize,$this->Date_c);
			
			if (isset($this->histo)) $this->Puissance = $this->histo->getQuantity('PS');
		}
		
		$this->RW->Read = 1; 
		$this->RW->Insert = 1;
		$this->RW->Update = 1;
	}
	
	function edit()
	{
		$this->get($this->id(),2);
		$this->editing(true);
		$this->make_inputs();
	}
	
	function make_inputs()
	{
		//to overwrite
	}
}

//CLASSE DES POINTS DE LIVRAISON ELECTRIQUE
class PLelec_model extends PL_model
{
	public $statut;
	
	function __construct($idSite,$idPL=false,$Stat4=false,$histoSize=30,$Date_c = false)
	{
		parent::__construct('elec',$idSite,$idPL,$Stat4,$histoSize,$Date_c);
		$this->make_inputs();
	}
	
	function make_inputs()
	{		
		$this->form['idSite_PL_elec']->set('idPLelec','hidden');
		$this->form['NoPL']->set('Numero PL','text',12)->add_rule('required');
		$this->form['NoCompteur']->set('Compteur','text',10);
		$this->form['NoPolice']->set('Police','text',10);
		$this->form['idEtat']->set('Etat','id_select_E')->add_rule('required');
		$this->form['idTension']->set('Tension','id_select_E')->add_rule('required');
		$this->form['Transfo']->set('Transfo','text',10);
		$this->form['Annee']->set('Année','text',4);
		$this->form['Puissance']->set('Puissance','text',4);
		$this->form['DisjoncteurRegle']->set('Reglage Disjoncteur','text',10);
		$this->form['Commentaire']->set('Observations','textarea',1,20);
				
		$this->add_form('DeleteBTN')->set('Supprimer','submit','delete_PL_elec','Supprimer ce Point de Livraison ?');
		$this->add_form('SubmitBTN')->set('Enregistrer','submit','save_PL_elec');
		$this->add_form('EditBTN')->set('Modifier','submit','edit_PL_elec');
			
		if ($this->statut <= 2) // NOUVEAU, A VALIDER OU MODIFICATION
		{
			//DEFAULT VALUES :
			if ($this->statut < 2) $this->setdata('DateCreation',date('Y-m-d'));
			
			//DISPLAY PARAMETERS
			$display = array(
						'idSite_PL_elec'=> 'field',
						'NoCompteur' 	=> 'field',
						'NoPL' 		=> 'field',
						'idEtat' 	=> 'field',
						'idTension'	=> 'field',
						'Transfo' 	=> 'field',
						'Annee' 	=> 'field',
						'Puissance' 	=> 'field',
						'DisjoncteurRegle'=> 'field',
						'Commentaire' 	=> 'field',
						
						'SubmitBTN' 	=> 'field'
					);
					
			if ($this->statut == 1) $display['DeleteBTN'] = 'field'; //Premiere edition
			if ($this->statut == 2) $display['DeleteBTN'] = 'field'; //post modification
			if ($this->statut == 0) $this->setdata('Valide',1); //nouveau
			
			$this->fields_method($display);
			
		}
		else if ($this->statut >= 3) //DISPLAY
		{			
			$this->fields_method('display');
			
			if (($this->statut == 3)||(($this->statut == 4)&&($this->simplelogin->group() >= 3))) $butt = 'field';
			else $butt = 'hide';

			$display = array(
						'idSite_PL_elec'=> $butt,
						'EditBTN' 	=> $butt
					);
					
			$this->fields_method($display);
		}
	}
	
	function mix_down()
	{
		//si le numero de compteur est fourni le numero de PL n'est plus obligatoire
		if ($this->input->post('NoCompteur') != '') $this->form['NoPL']->rem_rule('required');
	}
	
	function info()
	{
		//$info['Type'] = 'Elec  ';
		$info['PL'] = $this->form['NoPL']->get();
		$info['Compteur'] = $this->form['NoCompteur']->get();
		$info['Tension'] = $this->form['idTension']->get();
		$info['Puissance'] = ($this->form['Puissance']->get())?$this->form['Puissance']->get().' kW':'';
		$info['Transfo'] = $this->form['Transfo']->get();
		$info['Etat'] = $this->form['idEtat']->get();
		$info['Annee'] = $this->form['Annee']->get();
		$info['Reglage Dis.'] = $this->form['DisjoncteurRegle']->get();
		$info['Observations'] = $this->form['Commentaire']->get();
		
		return $info;
	}
	
	function diag()
	{
		$val = null;
		
		//Pdisj
		if ($this->form['DisjoncteurRegle']->get() > 0) $val['Pdisj'] = 280 * $this->form['DisjoncteurRegle']->get(); //400*cos(0.8) = 280
		//Ptransfo
		if ($this->form['Puissance']->get() > 0) $val['Ptransfo'] = $this->form['Puissance']->get();
		//Pcontrat
		if ($this->histo->getQuantity('PS') > 0) $val['Psouscrite'] = $this->histo->getQuantity('PS');
		//Patteinte
		if ($this->histo->getQuantity('PA') > 0) $val['Patteinte'] = $this->histo->getQuantity('PA');
		
		return $val;
	}
}

//CLASSE DES POINTS DE LIVRAISON EAU
class PLeau_model extends PL_model
{
	public $statut;
	
	function __construct($idSite,$idPL=false,$Stat4=false,$histoSize=495,$Date_c = false)
	{
		parent::__construct('eau',$idSite,$idPL,$Stat4,$histoSize,$Date_c);
		$this->make_inputs();
	}
	
	function make_inputs()
	{		
		$this->form['idSite_PL_eau']->set('idPLeau','hidden');
		$this->form['NoPL']->set('Numero PL','text',12)->add_rule('required');
		$this->form['NoCompteur']->set('Compteur','text',10);
		$this->form['NoPolice']->set('Police','text',10);
		$this->form['UsageEau']->set('Usages','mlist_text_autre',10);
		//$this->form['UsageEau']->set('Usages','t_select_AE',13,'v')->add_rule('required');
		$this->form['Commentaire']->set('Observations','textarea',1,20);
				
		$this->add_form('DeleteBTN')->set('Supprimer','submit','delete_PL_eau','Supprimer ce Point de Livraison ?');
		$this->add_form('SubmitBTN')->set('Enregistrer','submit','save_PL_eau');
		$this->add_form('EditBTN')->set('Modifier','submit','edit_PL_eau');
			
		
		if ($this->statut <= 2) // NOUVEAU, A VALIDER OU MODIFICATION
		{
			//DEFAULT VALUES :
			if ($this->statut < 2) $this->setdata('DateCreation',date('Y-m-d'));
			
			//DISPLAY PARAMETERS
			$display = array(
						'idSite_PL_eau' => 'field',
						'NoCompteur' 	=> 'field',
						'NoPL' 		=> 'field',
						'UsageEau' 	=> 'field',
						
						'Commentaire' 	=> 'field',
						
						'SubmitBTN' 	=> 'field'
					);
					
			if ($this->statut == 1) $display['DeleteBTN'] = 'field'; //Premiere edition
			if ($this->statut == 2) $display['DeleteBTN'] = 'field'; //post modification
			if ($this->statut == 0) $this->setdata('Valide',1); //nouveau
			
			$this->fields_method($display);
			
		}
		else if ($this->statut >= 3) //DISPLAY
		{			
			$this->fields_method('display');
			
			if (($this->statut == 3)||(($this->statut == 4)&&($this->simplelogin->group() >= 3))) $butt = 'field';
			else $butt = 'hide';
			
			$display = array(
						'idSite_PL_eau'=> $butt,
						'EditBTN' 	=> $butt
					);
					
			$this->fields_method($display);
		}
	}
	
	function mix_down()
	{
		//si le numero de compteur est fourni le numero de PL n'est plus obligatoire
		if ($this->input->post('NoCompteur') != '') $this->form['NoPL']->rem_rule('required');
	}
	
	function info()
	{
		//$info['Type'] = 'Eau   ';
		$info['PL'] = $this->form['NoPL']->get();
		$info['Compteur'] = $this->form['NoCompteur']->get();
		$info['Usage'] = $this->form['UsageEau']->get();
		$info['Observations'] = $this->form['Commentaire']->get();
		
		return $info;
	}
}

//CLASSE DES POINTS DE LIVRAISON GASOIL
class PLgasoil_model extends PL_model
{
	public $statut;
	public $Equipement;
	
	function __construct($idSite,$idPL=false,$Stat4=false,$histoSize=495,$Date_c = false)
	{
		parent::__construct('gasoil',$idSite,$idPL,$Stat4,$histoSize,$Date_c);
				
		$this->make_inputs();
	}
		
	function make_inputs()
	{	
		$this->load_module('Site_PL_gasoil_Cuves',3);
		$this->load_module('Site_PL_gasoil_Materiel',3);
			
		$this->form['idSite_PL_gasoil']->set('idPLgasoil','hidden');
		$this->form['NoContrat']->set('Contrat','text',7)->add_rule('required');
		$this->form['ConsoAnnuelle']->set('Conso Annuelle','text',2);
		$this->form['ConsoReel']->set('Conso Reelle','check1');
		$this->form['Commentaire']->set('Observations','textarea',6,20);
					
		$mod = 'Site_PL_gasoil_Cuves';
		foreach($this->modules[$mod] as $id=>$obj)
		{
			$this->add_form($mod.'_Volume_'.$id,$obj->data->Volume)->set('Volume','text',2);
			$this->add_form($mod.'_Niveau_'.$id,$obj->data->Niveau)->set('Niveau','check');
			$this->add_form($mod.'_Releve_'.$id,$obj->data->Releve)->set('Relevé','check');
		}	
			
		$mod = 'Site_PL_gasoil_Materiel';
		foreach($this->modules[$mod] as $id=>$obj)
		{
			$this->add_form($mod.'_idMaterielGasoil_'.$id,$obj->data->idMaterielGasoil)->set('MaterielGasoil','id_select_E','idMaterielGasoil');
			$this->add_form($mod.'_Marque_'.$id,$obj->data->Marque)->set('Marque','text',5);
			$this->add_form($mod.'_Puissance_'.$id,$obj->data->Puissance)->set('Puissance','text',2);
			$this->add_form($mod.'_Annee_'.$id,$obj->data->Annee)->set('Année','text',3);
			$this->add_form($mod.'_HeureNbr_'.$id,$obj->data->HeureNbr)->set('Nombre d\'heure','text',3);
			$this->add_form($mod.'_CompteurEauChaude_'.$id,$obj->data->CompteurEauChaude)->set('Compteur eau chaude','check1');
			
			if ($obj->data->idMaterielGasoil == 1) $this->Equipement['Groupe_elec']++;
			elseif ($obj->data->idMaterielGasoil == 2) $this->Equipement['Chaudiere']++;
		}
				
		$this->add_form('DeleteBTN')->set('Supprimer','submit','delete_PL_gasoil','Supprimer ce Point de Livraison ?');
		$this->add_form('SubmitBTN')->set('Enregistrer','submit','save_PL_gasoil');
		$this->add_form('EditBTN')->set('Modifier','submit','edit_PL_gasoil');
			
		
		if ($this->statut <= 2) // NOUVEAU, A VALIDER OU MODIFICATION
		{
			//DEFAULT VALUES :
			if ($this->statut < 2) $this->setdata('DateCreation',date('Y-m-d'));
			
			//DISPLAY PARAMETERS
			$display = array(
						'idSite_PL_gasoil' => 'field',
						'NoContrat' 	=> 'field',
						'ConsoAnnuelle'	=> 'field',
						'ConsoReel' 	=> 'field',
						'Commentaire' 	=> 'field',
						
						'SubmitBTN' 	=> 'field'
					);
					
			if ($this->statut == 1) $display['DeleteBTN'] = 'field'; //Premiere edition
			if ($this->statut == 2) $display['DeleteBTN'] = 'field'; //post modification
			if ($this->statut == 0) $this->setdata('Valide',1); //nouveau
			
			$this->fields_method($display);
			
			$mod = 'Site_PL_gasoil_Cuves';
			foreach($this->modules[$mod] as $id=>$obj)
			{
				$this->form[$mod.'_Volume_'.$id]->field();
				$this->form[$mod.'_Niveau_'.$id]->field();
				$this->form[$mod.'_Releve_'.$id]->field();
			}
			
			
			$mod = 'Site_PL_gasoil_Materiel';
			foreach($this->modules[$mod] as $id=>$obj) 
			{
				$this->form[$mod.'_idMaterielGasoil_'.$id]->field();
				$this->form[$mod.'_Marque_'.$id]->field();
				$this->form[$mod.'_Annee_'.$id]->field();
				$this->form[$mod.'_Puissance_'.$id]->field();
				$this->form[$mod.'_HeureNbr_'.$id]->field();
				$this->form[$mod.'_CompteurEauChaude_'.$id]->field();
			}
			
		}
		else if ($this->statut >= 3) //DISPLAY
		{			
			$this->fields_method('display');
			
			if (($this->statut == 3)||(($this->statut == 4)&&($this->simplelogin->group() >= 3))) $butt = 'field';
			else $butt = 'hide';
			
			$display = array(
						'idSite_PL_gasoil'=> $butt,
						'EditBTN' 	=> $butt
					);
					
			$this->fields_method($display);
		}
	}
	
	function mix_up() //après validation, avant enregistrement 
	{
		foreach($this->modules['Site_PL_gasoil_Cuves'] as $i=>$mod)
		{
			if ($mod->data->Volume == 0) 
			{
				$mod->remove();
				unset($this->modules['Site_PL_gasoil_Cuves'][$i]);
			}
		}
		
		foreach($this->modules['Site_PL_gasoil_Materiel'] as $i=>$mod)
		{
			if (($mod->data->idMaterielGasoil == '')&&($mod->data->Marque == ''))
			{
				$mod->remove();
				unset($this->modules['Site_PL_gasoil_Materiel'][$i]);
			}
		}
	}
	
	function info()
	{
		//$info['Type'] = 'Gasoil';
		$info['Contrat'] = $this->form['NoContrat']->get();
		if ($this->form['ConsoAnnuelle']->get())
		{
			$info['Conso'] = $this->form['ConsoAnnuelle']->get().' m3/an - ';
			$info['Conso'] .= ($this->form['ConsoReel']->get())?$this->form['ConsoReel']->get('*NULL','print').' Verifiée':'Non Verifiée';
		}
		
		$info['Cuves'] = '';
		$mod = 'Site_PL_gasoil_Cuves';
		foreach($this->modules[$mod] as $id=>$obj)
		{
			if ($this->form[$mod.'_Volume_'.$id]->get() > 0):
				if ($info['Cuves']) $info['Cuves'] .= '<br />';
				$info['Cuves'] .= $this->form[$mod.'_Volume_'.$id]->get().' m3';
				$info['Cuves'] .= ' - Niveau '.$this->form[$mod.'_Niveau_'.$id]->get('*NULL','print');
				$info['Cuves'] .= ' - Relevé '.$this->form[$mod.'_Releve_'.$id]->get('*NULL','print');
			endif;
		}
		
		$info['Equipements'] = '';
		$mod = 'Site_PL_gasoil_Materiel';
		foreach($this->modules[$mod] as $id=>$obj) 
		{
			if ($this->form[$mod.'_idMaterielGasoil_'.$id]->get()):
				if ($info['Equipements']) $info['Equipements'] .= '<br />';
				$info['Equipements'] .= $this->form[$mod.'_idMaterielGasoil_'.$id]->get();
				$info['Equipements'] .= ($this->form[$mod.'_Puissance_'.$id]->get())?' '.$this->form[$mod.'_Puissance_'.$id]->get().' kW':'';
				$info['Equipements'] .= ($this->form[$mod.'_Marque_'.$id]->get())?' - '.$this->form[$mod.'_Marque_'.$id]->get():'';
				$info['Equipements'] .= ($this->form[$mod.'_Annee_'.$id]->get())?' ('.$this->form[$mod.'_Annee_'.$id]->get().')':'';
				$info['Equipements'] .= ($this->form[$mod.'_HeureNbr_'.$id]->get())?' - '.$this->form[$mod.'_HeureNbr_'.$id]->get().' h':'';
				$info['Equipements'] .= ($this->form[$mod.'_CompteurEauChaude_'.$id]->get())?' - Compteur Eau '.$this->form[$mod.'_CompteurEauChaude_'.$id]->get('*NULL','print'):'';
			endif;
		}
		
		$info['Observations'] = $this->form['Commentaire']->get();
		
		return $info;
	}
}
?>
