<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('crudentry_model.php');

//CLASSE COMMUNE AU EXTENSION DU BATIMENT
class BatAux_model extends Crudentry_model
{
	public $statut;
	private $table;
	private $idBatiment;
	
	function __construct($table)
	{
		parent::__construct();
		
		//TODO DESCRIBE HERE MANUAL DEFAULTS VALUES !
		
		$this->table = $table;
		$this->init($table);
		$this->statut = 0;
		$this->make_inputs();
	}

	
	function get($idBatiment,$forced_statut=false)
	{		
		$this->idBatiment = $idBatiment;
		$this->db->where(array('idBatiment' => $idBatiment, 'DateFin IS NULL' => null));
		$this->db->order_by('DateCreation desc');
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0) 
		{
			$idBat = $query->row()->{'id'.$this->table}; 
			$this->load($idBat);
		}
		
		$this->statut = $forced_statut; //STATUT FORCE (aligné sur le batiment)
		
		$this->RW->Read = 1; 
		$this->RW->Insert = 1;
		$this->RW->Update = 1;

		$this->make_inputs();
	}
	
	function process() //get & execute action !
	{ 
		switch($this->action)
		{
			case 'save_bat':
			case 'step_bat':
				if (is_null($this->data->idBatiment)) $this->setdata('idBatiment',$this->idBatiment);			
				if ($this->checkfields()) return true;
				return false;
			break;
			return false;
		}
	}
	
	function make_inputs()
	{
			//to override
	}
}

//CLASSE OCCUPATION
class Bat_Occupation_model extends BatAux_model
{	
	function __construct()
	{
		parent::__construct('Bat_Occupation');
	}
	
	function make_inputs()
	{		
		$this->form['Occupation']->set('Type d\'Occupation','t_select_AE',13,'v')->add_rule('required');
		$this->form['Horaires']->set('Horaires','text',10);
		$this->form['JoursNbr']->set('Nombre de jours d\'occupation','text',3)->add_rule('required|integer');
		$this->form['OccupantNbr']->set('Nombre Occupant','text',3)->add_rule('required|integer');
		$this->form['OccupantNbr2']->set('Nombre Elèves','text',3);
		
		if ($this->statut == 1) $this->setdata('DateCreation',date('Y-m-d'));			
		else if ($this->statut == 2)
		{			
			//DISPLAY PARAMETERS
			$display = array(
						'Occupation'	=> 'field',
						'Horaires' 	=> 'field',
						'JoursNbr' 	=> 'field',
						'OccupantNbr' 	=> 'field',
						'OccupantNbr2' => 'field',
					);
					
			$this->fields_method($display);
		}
		else if ($this->statut >= 3) $this->fields_method('display');
	}
	
	function info()
	{
		$info['Occupants'] = $this->form['OccupantNbr']->get();
		$info['Occupants2'] = $this->form['OccupantNbr2']->get();
		$info['Occupation'] = $this->form['Occupation']->get();
		$info['Jours'] = $this->form['JoursNbr']->get();
		$info['Horaires'] = $this->form['Horaires']->get();
		
		return $info;
	}
}

//CLASSE ASPECT TECHNIQUE
class Bat_Technique_model extends BatAux_model
{	
	function __construct()
	{
		parent::__construct('Bat_Technique');
	}
	
	function make_inputs()
	{		
		$this->form['idEtatApparent']->set('Etat Apparent','id_select_E')->add_rule('required');
		$this->form['ToitureForme']->set('Forme de Toiture','t_select_AE',10);
		$this->form['ToitureType']->set('Type de Toiture','t_select_AE',10);
		$this->form['EvacuationPluie']->set('Evacuation Pluie','t_select_AE',12,'v');
		$this->form['AscenceurNbr']->set('Nombre d\'ascenceurs','text',3)->add_rule('integer');
		$this->form['MaintenanceElec']->set('Maintenance','t_select_AE',13,'Service');
		$this->form['MaintenanceElecPeriodicite']->set('Periodicite de la Maintenance','t_select_AE',13,'Periodicite');	

		if ($this->statut == 1) $this->setdata('DateCreation',date('Y-m-d'));
		else if ($this->statut == 2)
		{			
			//DISPLAY PARAMETERS
			$display = array(
						'idEtatApparent'=> 'field',
						'ToitureForme' 	=> 'field',
						'ToitureType' 	=> 'field',
						'EvacuationPluie'=> 'field',
						'AscenceurNbr' 	=> 'field',
						'MaintenanceElec' => 'field',
						'MaintenanceElecPeriodicite' => 'field',
					);
					
			$this->fields_method($display);
		}
		else if ($this->statut >= 3) $this->fields_method('display');
	}
	
	function info()
	{
		$info['Etat Apparent'] = $this->form['idEtatApparent']->get();
		$info['Toiture'] = $this->form['ToitureForme']->get();
		$info['Type'] = $this->form['ToitureType']->get();
		$info['Pluie'] = $this->form['EvacuationPluie']->get();
		$info['Ascenceur'] = $this->form['AscenceurNbr']->get();

		if (trim($this->form['MaintenanceElec']->get()) != '') $info['MaintenanceElec'] = $this->form['MaintenanceElec']->get().' / Periodicite : '.$this->form['MaintenanceElecPeriodicite']->get();

		return $info;
	}
}

//CLASSE EQUIPEMENT AIR
class Bat_Equip_Air_model extends BatAux_model
{	
	public $Equipement;
	
	function __construct()
	{
		parent::__construct('Bat_Equip_Air');
	}
	
	function make_inputs()
	{		
		$this->form['FroidMarque']->set('Marque Froid service','text',10);
		$this->form['FroidNombre']->set('Nombre Froid service','text',3)->add_rule('integer');
		$this->form['FroidModele']->set('Modèle Froid service','text',10);
		$this->form['FroidPuissance']->set('Puissance Froid secours','text',5)->add_rule('numeric');
		$this->form['FroidMarqueSecour']->set('Marque Froid secours','text',10);
		$this->form['FroidNombreSecour']->set('Nombre Froid secours','text',3)->add_rule('integer');
		$this->form['FroidModeleSecour']->set('Modèle Froid secours','text',10);
		$this->form['FroidPuissanceSecour']->set('Puissance Froid service','text',5)->add_rule('numeric');
		$this->form['FroidMarqueHS']->set('Marque Froid HS','text',10);
		$this->form['FroidNombreHS']->set('Nombre Froid HS','text',3)->add_rule('integer');
		$this->form['FroidModeleHS']->set('Modèle Froid HS','text',10);
		$this->form['FroidPuissanceHS']->set('Puissance Froid HS','text',5)->add_rule('numeric');
		$this->form['TraitementMarque']->set('Marque Traitement','text',10);
		$this->form['TraitementNombre']->set('Nombre Traitement','text',3)->add_rule('integer');
		$this->form['TraitementModele']->set('Modèle Traitement','text',10);
		$this->form['TraitementPuissance']->set('Puissance Traitement','text',5)->add_rule('numeric');
		$this->form['VentiloConvecteursNombre']->set('Nombre Ventilo-Convecteurs','text',3)->add_rule('integer');
		$this->form['Installation']->set('Conduite des Installations','t_select_AE',13,'Service');
		$this->form['Maintenance']->set('Conduite de la Maintenance','t_select_AE',13,'Service');
		$this->form['MaintenancePeriodicite']->set('Periodicite de la Maintenance','t_select_AE',13,'Periodicite');
		
		if ($this->statut == 1) $this->setdata('DateCreation',date('Y-m-d'));
		else if ($this->statut == 2)
		{			
			//DISPLAY PARAMETERS					
			$this->fields_method('field');
			
			$display = array(
						'idBat_Equip_Air'=> 'hide',
						'idBatiment' 	=> 'hide',
						'DateCreation' 	=> 'hide',
						'DateFin' 	=> 'hide'
					);
					
			$this->fields_method($display);
		}
		else if ($this->statut >= 3) $this->fields_method('display');
		
		$this->Equipement['Groupe_froid'] = $this->data->FroidNombre;
	}
	
	function info()
	{
		$info = array(
				' ' 		=> array('Froid Service','Frois Secours','Froid HS','Centrales Air','Ventilo-Conv.'),
				'Nombre'	=> array($this->form['FroidNombre']->get(),$this->form['FroidNombreSecour']->get(),$this->form['FroidNombreHS']->get(),$this->form['TraitementNombre']->get(),$this->form['VentiloConvecteursNombre']->get()),
				'Marque'	=> array($this->form['FroidMarque']->get(),$this->form['FroidMarqueSecour']->get(),$this->form['FroidMarqueHS']->get(),$this->form['TraitementMarque']->get(),''),
				'Modèle'	=> array($this->form['FroidModele']->get(),$this->form['FroidModeleSecour']->get(),$this->form['FroidModeleHS']->get(),$this->form['TraitementModele']->get(),''),
				'Puissance'	=> array($this->form['FroidPuissance']->get(),$this->form['FroidPuissanceSecour']->get(),$this->form['FroidPuissanceHS']->get(),$this->form['TraitementPuissance']->get(),'')
		);
		
		if (trim($this->form['Installation']->get()) != '') $info['Installation'] = $this->form['Installation']->get();
		if (trim($this->form['Maintenance']->get()) != '') $info['Maintenance'] = $this->form['Maintenance']->get().' / Periodicite : '.$this->form['MaintenancePeriodicite']->get();
		
		foreach($info['Puissance'] as $k=>$v) if ($v != '') $info['Puissance'][$k] .= ' kW';
		
		return $info;
	}
}

//CLASSE DEFINITION NIVEAU
class Bat_Niveaux_model extends Crudentry_model
{
	public $statut;
	private $idBatiment;
	private $Niveau;
	public $Aux;
	
// 	private $Pieces;
// 	private $Sanitaire;
// 	private $DistribElec;
// 	private $ElecClim;
// 	private $EclairPieces;
// 	private $EclairCouloir;
	
	function __construct($idBatiment,$Niv,$statut)
	{
		parent::__construct();
		
		$this->table = 'Bat_Niveau';
		$this->idBatiment = $idBatiment;
		$this->Niveau = $Niv;
		
		$this->init($this->table);
		$this->statut = $statut;
		$this->Aux = array();
		
		$this->get();
	}

	
	function get()
	{				
		$idBat = null;
		$this->db->where(array('idBatiment' => $this->idBatiment, 'Niveau' => $this->Niveau));
		$this->db->order_by('DateCreation desc');
		$query = $this->db->get($this->table);
		if ($query->num_rows() > 0) 
		{
			$idBat = $query->row()->{'id'.$this->table}; 
			$this->load($idBat);
		}
		
		$this->Aux['Pieces'] = new BN_Pieces($idBat,$this->Niveau,$this->statut);
		$this->Aux['Sanitaire'] = new BN_Sanitaire($idBat,$this->Niveau,$this->statut);
		$this->Aux['DistribElec'] = new BN_DistribElec($idBat,$this->Niveau,$this->statut);
		$this->Aux['ElecClim'] = new BN_ElecClim($idBat,$this->Niveau,$this->statut);
		$this->Aux['EclairPieces'] = new BN_EclairPieces($idBat,$this->Niveau,$this->statut);
		$this->Aux['EclairCouloir'] = new BN_EclairCouloir($idBat,$this->Niveau,$this->statut);
		
		$this->RW->Read = 1; 
		$this->RW->Insert = 1;
		$this->RW->Update = 1;

		$this->make_inputs();
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('Commentaire',false,$i)->set('Observations Niveau '.$i,'textarea',4,15);
		$this->add_form('Nom',false,$i)->set('Nom Niveau '.$i,'text',15);
		
		if ($this->statut == 1) $this->setdata('DateCreation',date('Y-m-d'));
		else if ($this->statut == 2) {$this->fields_method(array('Commentaire_'.$i => 'field')); $this->fields_method(array('Nom_'.$i => 'field'));}
		else if ($this->statut >= 3) {$this->fields_method(array('Commentaire_'.$i=>'display')); $this->fields_method(array('Nom_'.$i => 'display'));}
	}
	
	function formfields()
	{
		$fields = $this->u_formfields();		
		foreach ($this->Aux as $ty=>$obj) $fields = array_merge($fields,$obj->formfields());
		return $fields;
	}
	
	function process() //get & execute action !
	{ 
		switch($this->action)
		{
			case 'save_bat':
			case 'step_bat':
				if (is_null($this->data->Niveau)) $this->setdata('Niveau',$this->Niveau);
				if (is_null($this->data->idBatiment)) $this->setdata('idBatiment',$this->idBatiment);	
				$go = true;		
				if (!$this->checkfields()) $go = false;
				foreach($this->Aux as $ty=>$obj) if (!$obj->process()) $go = false;
				
				return $go;
			break;
			return false;
		}
	}
	
	function mix_up()
	{
		foreach($this->form as $name=>$Ffield)
		if (($Ffield->show == 'form')&&(isset($this->data_extra->{$name}))) 
			$this->setdata(str_replace('_'.$this->Niveau,'',$name),$this->data_extra->{$name});
	}
	
	function saveAll()
	{
		$this->save();
		foreach ($this->Aux as $ty=>$obj) 
		{
			if (!($this->Aux[$ty]->data->idBat_Niveau > 0)) 
			{
				$this->Aux[$ty]->data->idBat_Niveau = $this->id();
				$this->setdata('DateCreation',date('Y-m-d'));
			}
			$this->Aux[$ty]->save();
		}
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info = array('Nom' => $this->form['Nom_'.$i]->get());
		$ept = '  ';
		foreach ($this->Aux as $ty=>$obj) 
		{
			$ept .= ' ';
			$info = array_merge($info,array($ept=>''));
			$info = array_merge($info,$obj->info());
		}
		$info['Observations'] = $this->form['Commentaire_'.$i]->get();
		return $info;
	}
	
	function diag()
	{
		$val = array();		
		foreach ($this->Aux as $ty=>$obj)
		{
			$vs = $obj->diag();
			foreach($vs as $k=>$v) 
			{
				if(isset($val[$k])) $val[$k] += $v;
				else $val[$k] = $v;
			}
		} 

		$val['Pinstall'] = $val['Peclair']+$val['Pclim'];
		
		return $val;
	}
}

//CLASSE COMMUNE AUX EQUIPEMENT PAR NIVEAU
class BN_Aux extends Crudentry_model
{
	public $statut;
	protected $idBat;
	protected $Niveau;
	protected $table;
//	private $Pieces;
// 	private $Sanitaire;
// 	private $DistribElec;
// 	private $ElecClim;
// 	private $EclairPieces;
// 	private $EclairCouloir;
	
	function __construct($table,$idBat,$Niv,$statut)
	{
		parent::__construct();
		
		$this->table = $table;
		$this->idBat_Niveau = $idBat;
		$this->Niveau = $Niv;
		
		$this->init($this->table);
		$this->statut = $statut;
		
		$this->get();
	}

	
	function get()
	{		
		if (!is_null($this->idBat_Niveau))
		{
			$this->db->where(array('idBat_Niveau' => $this->idBat_Niveau, 'DateFin IS NULL' => null));
			$this->db->order_by('DateCreation desc');
			$query = $this->db->get($this->table);
			if ($query->num_rows() > 0) 
			{
				$idBat = $query->row()->{'id'.$this->table}; 
				$this->load($idBat);
			}
		}
		
		$this->RW->Read = 1; 
		$this->RW->Insert = 1;
		$this->RW->Update = 1;

		$this->make_inputs();
	}
	
	function make_inputs()
	{
		//overwrite	
	}

	function process() //get & execute action !
	{ 
		switch($this->action)
		{
			case 'save_bat':
			case 'step_bat':
				if (is_null($this->data->idBat_Niveau)) $this->setdata('idBat_Niveau',$this->idBat_Niveau);			
				if ($this->checkfields()) return true;
			break;
		}
		return false;
	}
	
	function mix_up()
	{
		foreach($this->form as $name=>$Ffield)
		if (($Ffield->show == 'form')&&(isset($this->data_extra->{$name}))) 
			$this->setdata(str_replace('_'.$this->Niveau,'',$name),$this->data_extra->{$name});
	}
	
	function diag()
	{
		$val = array();
		return $val;
	}
}

//CLASSE NBR DE PIECE PAR NIVEAU
class BN_Pieces extends BN_Aux
{
	public $pieces_nbr;
	
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_Pieces',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('BureauNombre',false,$i)->set('Nombre de Bureaux Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('ReunionNombre',false,$i)->set('Nombre de Salles de réunions Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('LogementNombre',false,$i)->set('Nombre de Logements / Dortoirs Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('HallNombre',false,$i)->set('Nombre de Hall / Accueil Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CantineNombre',false,$i)->set('Nombre de Cantine / Cafeteria Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('SportNombre',false,$i)->set('Nombre de Gymnase / Sport Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('LaboNombre',false,$i)->set('Nombre de Laboratoirs / Ateliers Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('ClasseNombre',false,$i)->set('Nombre de Salle de Classe Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('StockNombre',false,$i)->set('Nombre de Stock Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('InfirmerieNombre',false,$i)->set('Nombre de Infirmerie / Consultation Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('ChambreHopitalNombre',false,$i)->set('Nombre de Chambres d\'hôpital Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('PlateauTechNombre',false,$i)->set('Nombre de Plateau Techniques Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Autre1',false,$i)->set('Autre 1 Niveau '.$i,'text',6)->field();
		$this->add_form('Autre1Nombre',false,$i)->set('Nombre de Autre 1 Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Autre2',false,$i)->set('Autre 2 Niveau '.$i,'text',6)->field();
		$this->add_form('Autre2Nombre',false,$i)->set('Nombre de Autre 2 Niveau '.$i,'text',2)->add_rule('integer')->field();
		
		if ($this->statut >= 3) $this->fields_method('display');
		
		$this->pieces_nbr += $this->data->BureauNombre;
		$this->pieces_nbr += $this->data->ReunionNombre;
		$this->pieces_nbr += $this->data->LogementNombre;
		$this->pieces_nbr += $this->data->HallNombre;
		$this->pieces_nbr += $this->data->CantineNombre;
		$this->pieces_nbr += $this->data->SportNombre;
		$this->pieces_nbr += $this->data->LaboNombre;
		$this->pieces_nbr += $this->data->ClasseNombre;
		$this->pieces_nbr += $this->data->StockNombre;
		$this->pieces_nbr += $this->data->InfirmerieNombre;
		$this->pieces_nbr += $this->data->ChambreHopitalNombre;
		$this->pieces_nbr += $this->data->PlateauTechNombre;
		$this->pieces_nbr += $this->data->Autre1Nombre;
		$this->pieces_nbr += $this->data->Autre2Nombre;
	}
	
	
	function info()
	{
		$i = $this->Niveau;
		$info['Pieces'] = '.';
		$info['Bureau'] = $this->form['BureauNombre_'.$i]->get();
		$info['Reunion'] = $this->form['ReunionNombre_'.$i]->get();
		$info['Logement'] = $this->form['LogementNombre_'.$i]->get();
		$info['Hall'] = $this->form['HallNombre_'.$i]->get();
		$info['Cantine'] = $this->form['CantineNombre_'.$i]->get();
		$info['Sport'] = $this->form['SportNombre_'.$i]->get();
		$info['Labo'] = $this->form['LaboNombre_'.$i]->get();
		$info['Classe'] = $this->form['ClasseNombre_'.$i]->get();
		$info['Stock'] = $this->form['StockNombre_'.$i]->get();
		$info['Infirmerie'] = $this->form['InfirmerieNombre_'.$i]->get();
		$info['Chambres H'] = $this->form['ChambreHopitalNombre_'.$i]->get();
		$info['Plateau Tech'] = $this->form['PlateauTechNombre_'.$i]->get();
		if ($this->form['Autre1_'.$i]->get() != '') $info[$this->form['Autre1_'.$i]->get()] = ($this->form['Autre1Nombre_'.$i]->get() > 0)?$this->form['Autre1Nombre_'.$i]->get():1;
		if ($this->form['Autre2_'.$i]->get() != '') $info[$this->form['Autre2_'.$i]->get()] = ($this->form['Autre2Nombre_'.$i]->get() > 0)?$this->form['Autre2Nombre_'.$i]->get():1;

		
		return $info;
	}
	
	function diag()
	{
		$i = $this->Niveau;
		
		$val['Npieces'] = $this->form['BureauNombre_'.$i]->get()+
					$this->form['ReunionNombre_'.$i]->get()+
					$this->form['LogementNombre_'.$i]->get()+
					$this->form['HallNombre_'.$i]->get()+
					$this->form['CantineNombre_'.$i]->get()+
					$this->form['SportNombre_'.$i]->get()+
					$this->form['LaboNombre_'.$i]->get()+
					$this->form['ClasseNombre_'.$i]->get()+
					$this->form['StockNombre_'.$i]->get()+
					$this->form['InfirmerieNombre_'.$i]->get()+
					$this->form['ChambreHopitalNombre_'.$i]->get()+
					$this->form['PlateauTechNombre_'.$i]->get()+
					$this->form['Autre1Nombre_'.$i]->get()+
					$this->form['Autre2Nombre_'.$i]->get();
					
		return $val;
	}
}

//CLASSE SANITAIRE PAR NIVEAU
class BN_Sanitaire extends BN_Aux
{
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_Sanitaire',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('SanitaireNombre',false,$i)->set('Nombre de Sanitaire Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('WcNombre',false,$i)->set('Nombre de Wc Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('UrinoirNombre',false,$i)->set('Nombre de Urinoir Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('LavaboNombre',false,$i)->set('Nombre de Lavabo Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('DoucheNombre',false,$i)->set('Nombre de Douche Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('SAutre1',false,$i)->set('Sanitaire Autre 1 Niveau '.$i,'text',6)->field();
		$this->add_form('SAutre1Nombre',false,$i)->set('Nombre de Sanitaire Autre 1 Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('idEtatApparent',false,$i)->set('Etat Apparent Niveau '.$i,'id_select_E','idEtatApparent')->field();
		
		if ($this->statut >= 3) $this->fields_method('display');
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info['Sanitaires'] = '.';
		$info['Etat'] = $this->form['idEtatApparent_'.$i]->get();
		$info['Nombre'] = $this->form['SanitaireNombre_'.$i]->get();
		$info['WC'] = $this->form['WcNombre_'.$i]->get();
		$info['Urinoir'] = $this->form['UrinoirNombre_'.$i]->get();
		$info['Lavabo'] = $this->form['LavaboNombre_'.$i]->get();
		$info['Douche'] = $this->form['DoucheNombre_'.$i]->get();
		$info['Nombre'] = $this->form['SanitaireNombre_'.$i]->get();
		if ($this->form['SAutre1_'.$i]->get() != '') $info[$this->form['SAutre1_'.$i]->get()] = ($this->form['SAutre1Nombre_'.$i]->get() > 0)?$this->form['SAutre1Nombre_'.$i]->get():1;
		
		return $info;
	}
}

//CLASSE DISTRIBUTION ELECTRIQUE PAR NIVEAU
class BN_DistribElec extends BN_Aux
{
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_DistribElec',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('TableauFusible',false,$i)->set('Tableau div Fusible Niveau '.$i,'yesno',2)->field();
		$this->add_form('TableauDisjoncteur',false,$i)->set('Tableau div Disjoncteur Niveau '.$i,'yesno',2)->field();
		$this->add_form('ElecAutre',false,$i)->set('Elec Autre Niveau '.$i,'text',6)->field();
		$this->add_form('ArmoireNombre',false,$i)->set('Nombre Armoire elec Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('ElecSchema',false,$i)->set('Schema elec '.$i,'yesno',2)->field();
		$this->add_form('SeparationElecClim',false,$i)->set('Separation elec '.$i,'yesno',2)->field();
		$this->add_form('ReperageAppareil',false,$i)->set('Reperage elec Autre 1 Niveau '.$i,'yesno',2)->field();
		$this->add_form('idArmoireEtat',false,$i)->set('Etat Apparent Niveau '.$i,'id_select_0','idEtatApparent')->field();


		if ($this->statut >= 3) $this->fields_method('display');
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info['Electricité'] = '.';
		$info['Etat'] = $this->form['idArmoireEtat_'.$i]->get();
		$info['Tabl. Fusible'] = $this->form['TableauFusible_'.$i]->get('*NULL','print');
		$info['Tabl. Disj.'] = $this->form['TableauDisjoncteur_'.$i]->get('*NULL','print');
		$info['Autre Distri'] = $this->form['ElecAutre_'.$i]->get('*NULL','print');
		$info['Armoire'] = $this->form['ArmoireNombre_'.$i]->get();
		$info['Schema'] = $this->form['ElecSchema_'.$i]->get('*NULL','print');
		$info['Separation'] = $this->form['SeparationElecClim_'.$i]->get('*NULL','print');
		$info['Reperage'] = $this->form['ReperageAppareil_'.$i]->get('*NULL','print');

		
		return $info;
	}
}

//CLASSE EQUIPEMENT ELEC / CLIM PAR NIVEAU
class BN_ElecClim extends BN_Aux
{
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_ElecClim',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('OrdinateurNombre',false,$i)->set('Nombre Ordinateur Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('ImprimanteNombre',false,$i)->set('Nombre Imprimante Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('PhotocopieuseNombre',false,$i)->set('Nombre Photocopieuse Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('BrasseurNombre',false,$i)->set('Nombre Brasseur Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('SplitPuissance',false,$i)->set('Puissance Split Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('SplitNombre',false,$i)->set('Nombre Split Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Split2Puissance',false,$i)->set('Puissance Split2 Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('Split2Nombre',false,$i)->set('Nombre Split2 Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('BowPuissance',false,$i)->set('Puissance Bow Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('BowNombre',false,$i)->set('Nombre Bow Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Bow2Puissance',false,$i)->set('Puissance Bow2 Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('Bow2Nombre',false,$i)->set('Nombre Bow2 Niveau '.$i,'text',2)->add_rule('integer')->field();
		
		
		if ($this->statut >= 3) $this->fields_method('display');
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info['Equipements'] = '.';
		$info['Ordinateur'] = $this->form['OrdinateurNombre_'.$i]->get();
		$info['Imprimante'] = $this->form['ImprimanteNombre_'.$i]->get();
		$info['Photocopieuse'] = $this->form['PhotocopieuseNombre_'.$i]->get();
		$info['Brasseur'] = $this->form['BrasseurNombre_'.$i]->get();
		
		$todo = array('Split 1'=>'Split','Split 2'=>'Split2','Bow 1'=>'Bow','Bow 2'=>'Bow2');
		foreach ($todo as $k=>$v)
		{
			$info[$k] = '';
			if ($this->form[$v.'Puissance_'.$i]->get() != '') $info[$k] .= $this->form[$v.'Puissance_'.$i]->get().'W ';
			if ($this->form[$v.'Nombre_'.$i]->get() != 0) $info[$k] .= $this->form[$v.'Nombre_'.$i]->get().' (Nbr)';
		}
		return $info;
	}
	
	function diag()
	{
		$i = $this->Niveau;
		
		//WARNING, TODO : DEFINE IN CONFIG
		$def['Split'] = 1500; //W
		$def['Bow'] = 1000; //W
		
		$val['Pclim'] = 0;
		$val['Nclim'] = 0;
		
		$todo = array('Split 1'=>'Split','Split 2'=>'Split2','Bow 1'=>'Bow','Bow 2'=>'Bow2');
		foreach ($todo as $k=>$v)
		{
			list($type,) = explode(' ',$k);
			if($this->form[$v.'Puissance_'.$i]->get()) $P = $this->form[$v.'Puissance_'.$i]->get();
			else $P = $def[$type];
			
			//Si la puissance est inférieure à 50W, c'est qu'elle est en kW
			if ($P < 50) $P = $P*1000;
			
			$val['Pclim'] += $P*$this->form[$v.'Nombre_'.$i]->get();
			$val['Nclim'] += $this->form[$v.'Nombre_'.$i]->get();
		}
		
		return $val;
	}
}

//CLASSE ECLAIRAGE PIEC PAR NIVEAU
class BN_EclairPieces extends BN_Aux
{
	public $eclairage_nbr;
	
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_EclairPieces',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('IncandescNombre',false,$i)->set('Nombre Ampoule Incand Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('BasseConsoNombre',false,$i)->set('Nombre Ampoule BC Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('LustreNombre',false,$i)->set('Nombre Lustre Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('LustrePuissance',false,$i)->set('Puissance Lustre BC Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('Neon1Taille',false,$i)->set('Taille Neon 1 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon1Nombre',false,$i)->set('Nombre Neon 1 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon1PU',false,$i)->set('Puissance unitaire Neon 1 '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('Neon1HS',false,$i)->set('% HS Neon 1 '.$i,'text',2)->field();
		$this->add_form('Neon2Taille',false,$i)->set('Taille Neon 2 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon2Nombre',false,$i)->set('Nombre Neon 2 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon2PU',false,$i)->set('Puissance unitaire Neon 2 '.$i,'text',2)->field();
		$this->add_form('Neon2HS',false,$i)->set('% HS Neon 2 '.$i,'text',2)->field();
		$this->add_form('Neon3Taille',false,$i)->set('Taille Neon 3 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon3Nombre',false,$i)->set('Nombre Neon 3 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon3PU',false,$i)->set('Puissance unitaire Neon 3 '.$i,'text',2)->field();
		$this->add_form('Neon3HS',false,$i)->set('% HS Neon 3 '.$i,'text',2)->field();
		$this->add_form('Neon4Taille',false,$i)->set('Taille Neon 4 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon4Nombre',false,$i)->set('Nombre Neon 4 '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('Neon4PU',false,$i)->set('Puissance unitaire Neon 4 '.$i,'text',2)->field();
		$this->add_form('Neon4HS',false,$i)->set('% HS Neon 4 '.$i,'text',2)->field();
		
		if ($this->statut >= 3) $this->fields_method('display');
		
		$this->eclairage_nbr += $this->data->IncandescNombre;
		$this->eclairage_nbr += $this->data->BasseConsoNombre;
		$this->eclairage_nbr += $this->data->LustreNombre;
		$this->eclairage_nbr += $this->data->Neon1Nombre;
		$this->eclairage_nbr += $this->data->Neon2Nombre;
		$this->eclairage_nbr += $this->data->Neon3Nombre;
		$this->eclairage_nbr += $this->data->Neon4Nombre;
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info['Ecl. Pièces'] = '.';
		$info['Incandescence'] = $this->form['IncandescNombre_'.$i]->get();
		$info['Basse Conso'] = $this->form['BasseConsoNombre_'.$i]->get();
		$info['Lustre'] = ($this->form['LustreNombre_'.$i]->get() > 0)?$this->form['LustreNombre_'.$i]->get().'x'.$this->form['LustrePuissance_'.$i]->get().'kW':'';

		$nbr = 4;
		for($k=1;$k<=$nbr;$k++)
		{
			$info['Bloc Neon '.$k] = '';
			if ($this->form['Neon'.$k.'Taille_'.$i]->get() > 0) $info['Bloc Neon '.$k] .= 'T:'.$this->form['Neon'.$k.'Taille_'.$i]->get().' ';
			if ($this->form['Neon'.$k.'Nombre_'.$i]->get() > 0) $info['Bloc Neon '.$k] .= 'n:'.$this->form['Neon'.$k.'Nombre_'.$i]->get().' ';
			if ($this->form['Neon'.$k.'PU_'.$i]->get() > 0) $info['Bloc Neon '.$k] .= 'PU:'.$this->form['Neon'.$k.'PU_'.$i]->get().' ';
			if ($this->form['Neon'.$k.'HS_'.$i]->get() > 0) $info['Bloc Neon '.$k] .= '%HS:'.$this->form['Neon'.$k.'HS_'.$i]->get();
		}
		
		return $info;
	}
	
	function diag()
	{
		$i = $this->Niveau;
		
		//WARNING, TODO : DEFINE IN CONFIG !!
		$Pinc = 60; //W
		$Pbc = 11; //W
		
		$val['Peclair'] = 0;
		$val['Neclair'] = 0;
		$val['PeclairIncandesc'] = 0;
		$val['NeclairIncandesc'] = 0;
		$val['PeclairBasseConso'] = 0;
		$val['NeclairBasseConso'] = 0;
		$val['PeclairLustre'] = 0;
		$val['NeclairLustre'] = 0;
		$val['PeclairNeon'] = 0;
		$val['NeclairNeon'] = 0;
				
		//INCAND
		$val['PeclairIncandesc'] += $this->form['IncandescNombre_'.$i]->get()*$Pinc;
		$val['NeclairIncandesc'] += $this->form['IncandescNombre_'.$i]->get();
		
		//BC
		$val['PeclairBasseConso'] += $this->form['BasseConsoNombre_'.$i]->get()*$Pbc;
		$val['NeclairBasseConso'] += $this->form['BasseConsoNombre_'.$i]->get();
		
		//LUSTRES
		$val['PeclairLustre'] += $this->form['LustreNombre_'.$i]->get()*$this->form['LustrePuissance_'.$i]->get();
		$val['NeclairLustre'] += $this->form['LustreNombre_'.$i]->get();
		
		//NEONS
		$nbr = 4;
		for($k=1;$k<=$nbr;$k++)
		{
			$val['PeclairNeon'] += $this->form['Neon'.$k.'Taille_'.$i]->get()*$this->form['Neon'.$k.'Nombre_'.$i]->get()*$this->form['Neon'.$k.'PU_'.$i]->get();
			$val['NeclairNeon'] += $this->form['Neon'.$k.'Nombre_'.$i]->get();
		}
		
		//Tout eclairage
		
		$val['Peclair'] = $val['PeclairIncandesc']+$val['PeclairBasseConso']+$val['PeclairLustre']+$val['PeclairNeon'];
		$val['Neclair'] = $val['NeclairIncandesc']+$val['NeclairBasseConso']+$val['NeclairLustre']+$val['NeclairNeon'];
		
		return $val;
	}
}

//CLASSE ECLAIRAGE COULOIR PAR NIVEAU
class BN_EclairCouloir extends BN_Aux
{
	public $eclairage_nbr;
	
	function __construct($idBat,$Niv,$statut)
	{
		parent::__construct('Bat_Niveau_EclairCouloir',$idBat,$Niv,$statut);
	}
	
	function make_inputs()
	{
		$i = $this->Niveau;
		$this->add_form('CIncandescNombre',false,$i)->set('Nombre Ampoule Incand couloir Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CBasseConsoNombre',false,$i)->set('Nombre Ampoule BC couloir Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CLustreNombre',false,$i)->set('Nombre Lustre couloir Niveau '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CLustrePuissance',false,$i)->set('Puissance Lustre BC couloir Niveau '.$i,'text',2)->add_rule('numeric')->field();
		$this->add_form('CNeon1Taille',false,$i)->set('Taille Neon 1 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon1Nombre',false,$i)->set('Nombre Neon 1 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon1PU',false,$i)->set('Puissance unitaire Neon 1 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon1HS',false,$i)->set('% HS Neon 1 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon2Taille',false,$i)->set('Taille Neon 2 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon2Nombre',false,$i)->set('Nombre Neon 2 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon2PU',false,$i)->set('Puissance unitaire Neon 2 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon2HS',false,$i)->set('% HS Neon 2 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon3Taille',false,$i)->set('Taille Neon 3 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon3Nombre',false,$i)->set('Nombre Neon 3 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon3PU',false,$i)->set('Puissance unitaire Neon 3 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon3HS',false,$i)->set('% HS Neon 3 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon4Taille',false,$i)->set('Taille Neon 4 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon4Nombre',false,$i)->set('Nombre Neon 4 couloir '.$i,'text',2)->add_rule('integer')->field();
		$this->add_form('CNeon4PU',false,$i)->set('Puissance unitaire Neon 4 couloir '.$i,'text',2)->field();
		$this->add_form('CNeon4HS',false,$i)->set('% HS Neon 4 couloir '.$i,'text',2)->field();
		$this->add_form('Detecteur',false,$i)->set('Detecteur couloir Niveau '.$i,'yesno',2)->field();
		$this->add_form('Interrupteur',false,$i)->set('Interrupteur couloir Niveau '.$i,'yesno',2)->field();
		$this->add_form('AutoAutre',false,$i)->set('Autre Automatisme couloir Niveau '.$i,'text',2)->field();
		$this->add_form('Tempo',false,$i)->set('avec Tempo couloir Niveau '.$i,'yesno',2)->field();
		
		if ($this->statut >= 3) $this->fields_method('display');
		
		$this->eclairage_nbr += $this->data->IncandescNombre;
		$this->eclairage_nbr += $this->data->BasseConsoNombre;
		$this->eclairage_nbr += $this->data->LustreNombre;
		$this->eclairage_nbr += $this->data->Neon1Nombre;
		$this->eclairage_nbr += $this->data->Neon2Nombre;
		$this->eclairage_nbr += $this->data->Neon3Nombre;
		$this->eclairage_nbr += $this->data->Neon4Nombre;
	}
	
	function info()
	{
		$i = $this->Niveau;
		$info['Ecl. Couloirs'] = '.';
		$info['Incandescence '] = $this->form['CIncandescNombre_'.$i]->get();
		$info['Basse Conso '] = $this->form['CBasseConsoNombre_'.$i]->get();
		$info['Lustre '] = $this->form['CLustreNombre_'.$i]->get();
		
		$info['Detecteur'] = $this->form['Detecteur_'.$i]->get('*NULL','print');
		$info['Interrupteur'] = $this->form['Interrupteur_'.$i]->get('*NULL','print');
		$info['Tempo'] = $this->form['Tempo_'.$i]->get('*NULL','print');
		$info['Autre'] = $this->form['AutoAutre_'.$i]->get();
		

		$nbr = 4;
		for($k=1;$k<=$nbr;$k++)
		{
			$info['Bloc Neon '.$k.' '] = '';
			if ($this->form['CNeon'.$k.'Taille_'.$i]->get() > 0) $info['Bloc Neon '.$k.' '] .= 'T:'.$this->form['CNeon'.$k.'Taille_'.$i]->get().' ';
			if ($this->form['CNeon'.$k.'Nombre_'.$i]->get() > 0) $info['Bloc Neon '.$k.' '] .= 'n:'.$this->form['CNeon'.$k.'Nombre_'.$i]->get().' ';
			if ($this->form['CNeon'.$k.'PU_'.$i]->get() > 0) $info['Bloc Neon '.$k.' '] .= 'PU:'.$this->form['CNeon'.$k.'PU_'.$i]->get().' ';
			if ($this->form['CNeon'.$k.'HS_'.$i]->get() > 0) $info['Bloc Neon '.$k.' '] .= '%HS:'.$this->form['CNeon'.$k.'HS_'.$i]->get();
		}
		
		return $info;
	}
	
	function diag()
	{
		$i = $this->Niveau;
		
		//WARNING, TODO : DEFINE IN CONFIG !!
		$Pinc = 60; //W
		$Pbc = 11; //W
		
		$val['Peclair'] = 0;
		$val['Neclair'] = 0;
		$val['PeclairIncandesc'] = 0;
		$val['NeclairIncandesc'] = 0;
		$val['PeclairBasseConso'] = 0;
		$val['NeclairBasseConso'] = 0;
		$val['PeclairLustre'] = 0;
		$val['NeclairLustre'] = 0;
		$val['PeclairNeon'] = 0;
		$val['NeclairNeon'] = 0;
		
		//INCAND
		$val['PeclairIncandesc'] += $this->form['CIncandescNombre_'.$i]->get()*$Pinc;
		$val['NeclairIncandesc'] += $this->form['CIncandescNombre_'.$i]->get();

		//Lustre
		$val['PeclairLustre'] += $this->form['CLustreNombre_'.$i]->get()*$this->form['CLustrePuissance_'.$i]->get();
		$val['NeclairLustre'] += $this->form['CLustreNombre_'.$i]->get();
		
		//BC
		$val['PeclairBasseConso'] += $this->form['CBasseConsoNombre_'.$i]->get()*$Pbc;
		$val['NeclairBasseConso'] += $this->form['CBasseConsoNombre_'.$i]->get();
		
		//NEONS
		$nbr = 4;
		for($k=1;$k<=$nbr;$k++)
		{
			$val['PeclairNeon'] += $this->form['CNeon'.$k.'Taille_'.$i]->get()*$this->form['CNeon'.$k.'Nombre_'.$i]->get()*$this->form['CNeon'.$k.'PU_'.$i]->get();
			$val['NeclairNeon'] += $this->form['CNeon'.$k.'Nombre_'.$i]->get();
		}
		
		//Tout eclairage
		$val['Peclair'] = $val['PeclairIncandesc']+$val['PeclairBasseConso']+$val['PeclairLustre']+$val['PeclairNeon'];
		$val['Neclair'] = $val['NeclairIncandesc']+$val['NeclairBasseConso']+$val['NeclairLustre']+$val['NeclairNeon'];
		
		return $val;
	}
}
?>
