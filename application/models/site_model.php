<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once('crudentry_model.php');
include_once('pl_model.php');
include_once('batiment_model.php');
include_once('alerte_model.php');

class Site_model extends Crudentry_model
{
	public $statut;
	public $PLelec;
	public $PLeau;
	public $PLgasoil;
	public $Batiments;
	private $reload_ARGS;
	private $PLtypes;
	public $byPass_statut1;  //sauter l'étape 1 du recensement
	public $Ratio;
	public $Occupants;
	public $Occupants2;
	public $Surface;
	private $histoSize;
	public $onWork;
	private $Date_c;
	public $Alerte;
	public $Equipement;
	public $Nom;
	private $EwatchDB;
	//private $Days_Decalage; //nombre de jour de décalage entre date courante et maintenant -- positif si date courante < time()
	
	//CONSTRUCTION : CHARGE UN SITE VIERGE
	function __construct($Date_c,$enable_action=true)
	{
		parent::__construct($enable_action);

		$this->init('Site');
		$this->statut = 0;
		$this->make_inputs();
		$this->PLelec = array();
		$this->PLeau = array();
		$this->PLgasoil = array();
		$this->Alerte = array();
		
		$this->Occupants = 0;
		$this->Occupants2 = 0;
		
		$this->setHistoSize(495);
		$this->Date_c = $Date_c;
		$this->Days_Decalage = 0;
		
		$this->onWork = array();
		
		//ACCESS TO EWATCH CONSO
		$this->EwatchDB = $this->load->database('ewatch', TRUE);		
	}
	
	//CHARGE UN OBJET SITE : si cache existant, sinon chargement normal et cree une image cache 
	function lazy_get($idSite,$cache=-1)
	{
		if ($cache <= -1) $cache = $this->db->select('cache')->where('idSite',$idSite)->get('Site')->row()->cache;
	
		$obj = unserialize($cache);
		
		if ((is_object($obj)) && ($cache !== 0)) return $obj;
		else 
		{
			$this->get($idSite);		
			$fields = $this->formfields();
			
			$me = new stdClass;
						
			$me->{'Nom'} = $fields['Nom'];
			$me->{'Ministere'} = $fields['idMinistere'];
			
			
			$me->{'N_Bat'} = count($this->Batiments);
					
			$me->{'N_Elec'} = count($this->PLelec);
			foreach ($this->PLelec as $idPL=>$PL) $me->{'P_Elec'} += $PL->Puissance; //puissance souscrite
			
			$me->{'N_Eau'} = count($this->PLeau);
			foreach ($this->PLeau as $idPL=>$PL) $me->{'P_Eau'} += $PL->Puissance; //puissance souscrite
			
			$me->{'N_Gasoil'} = count($this->PLgasoil);
			
			$me->{'Ratio'} = $this->Ratio;
			$me->{'Surface'} = $this->Surface;
			$me->{'Occupants'} = $this->Occupants;
			$me->{'Occupants2'} = $this->Occupants2;
			
			$me->{'Alerte'} = array(
								$this->Alerte['RougeAlerts'],
								$this->Alerte['OrangeAlerts']
							);
			
			$me->{'Groupe_elec'} = $this->Equipement['Groupe_elec'];
			$me->{'Chaudiere'} = $this->Equipement['Chaudiere'];
			$me->{'Groupe_froid'} = $this->Equipement['Groupe_froid'];	
			
			$me->{'Niveau_sans_piece'} = $this->Bat_Problems['Pieces'];			
			$me->{'Niveau_sans_eclairage'} = $this->Bat_Problems['Eclairages'];
			
			$obj = serialize($me);
			$obj = str_replace("'","\'",$obj);
						
			//UN-TRACKED QUERY
			$this->db->simple_query("UPDATE `Site` SET `cache` = '".$obj."' WHERE `idSite` = '".$idSite."'");

			return $me;
		}
	}

	//CHARGE UN SITE DONNE
	function get($idSite,$forced_statut=false,$newPL=false,$byPass1=false,$Date_c = false)
	{				
		$this->byPass_statut1 = $byPass1;
		
		//changement de date courante
		if ($Date_c) $this->Date_c = $Date_c;
			//$this->Days_Decalage = round((time()-$Date_c)/86400); 

		
		$this->reload_ARGS = array($idSite,$forced_statut,$newPL,$byPass1,$Date_c);
		
		$this->RW->Read = 1; 
		$this->RW->Insert = 3;
		$this->RW->Update = 3;
		
		if($this->load($idSite))
		{
			if ($forced_statut) $this->statut = $forced_statut; //STATUT FORCE (reedition, light mode)
			else
			{
				$this->statut = 1; //SITE EXISTANT, VERIFIER NOM, ADRESSE ET EQUIPE
				if (($this->data->idEquipe != null)||($this->byPass_statut1)) $this->statut = 2; //RETOUR VISITE, SAISIE INFO SITE
				if ($this->data->DateVisite != null) $this->statut = 3; //SITE VALIDE
				if ($this->data->ValideManu == 1) $this->statut = 4; //CONSULT
				//Statut 5 : Light Mode, only forced
			}
			
			if ($this->data->ValideManu == null) $this->RW->Update = 1;
			if (strtotime($this->data->ValideDate) >= strtotime('-1 days',time())) $this->RW->Update = 1;
			
			//Recuperation des PL et Liste des Batiments
			if ($this->statut >= 3)
			{
			
				$this->build_PL('elec',$newPL,$this->histoSize);
				$this->build_PL('eau',$newPL,$this->histoSize);
				$this->build_PL('gasoil',$newPL,$this->histoSize);
				
				$this->build_Batiments($newPL);				
				
				if ($this->statut >= 4)
				{
					$this->build_Alertes();
					$this->build_Equipements();
				}
				
				if (!$newPL)
				{
					$this->Surface = 0;
					$this->Occupants = 0;
					$this->Occupants2 = 0;
					
					//NOMBRE D'OCCUPANT ET SURFACE TOTALE
					foreach($this->Batiments as $id=>$nom) 
					{
						$bat = $this->getEntry('Batiment',"idBatiment = '".$id."' AND DateFin IS NULL");
						if (isset($bat['SurfaceTotal'])) $this->Surface +=  $bat['SurfaceTotal'];
						
						$occ = $this->getEntry('Bat_Occupation',"idBatiment = '".$id."' AND DateFin IS NULL");
						if (isset($occ['OccupantNbr'])) $this->Occupants +=  $occ['OccupantNbr'];
						if (isset($occ['OccupantNbr2'])) $this->Occupants2 +=  $occ['OccupantNbr2'];
					}
					
					//GENERER LES RATIOS !
					
					////////////CLIM
					$diag=$this->diag();
					$this->Ratio['clim']['Puissance']=$diag['Pclim'];
					$this->Ratio['clim']['Puissance/m²']=$diag['Pclim/m²'];
					
					////////////EAU
					//EAU 30 JOURS
					if (($this->Surface > 0)&&($this->Occupants > 0))
					{				
						$Litres = 0;
						foreach($this->PLeau as $id=>$PL) 
						{	
							$Litres += $this->PLeau[$id]->histo->getQuantity('m3',30)*1000;
							//echo ($this->PLeau[$id]->histo->getQuantity('m3',30)*1000).'<br>';
						}
						
						$month = date("n",$this->Date_c);
						if (($this->data->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
							$this->Ratio['eau']['30j']['L/hab/J'] = round($Litres*100 / (30 * $this->Occupants))/100;	
						}
						else{
							$this->Ratio['eau']['30j']['L/hab/J'] = round($Litres*100 / (30 * ($this->Occupants+$this->Occupants2)))/100;
						}
						
						$this->Ratio['eau']['30j']['L/hab/J'] = round($Litres*100 / (30 * $this->Occupants))/100;
						$this->Ratio['eau']['30j']['m3'] = round($Litres/10)/100;
						
						if ($this->statut < 5)
						{
							$MF = 0; $CM3 = 0; 
							foreach($this->PLeau as $id=>$PL) 
							{
								$MF += $this->PLeau[$id]->histo->getQuantity('MF',30);
								$CM3 += $this->PLeau[$id]->histo->getQuantity('CM3',30)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['eau']['30j']['PS'][$id] = $this->PLeau[$id]->histo->getQuantity('PS',1);
							}
							
							$this->Ratio['eau']['30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['eau']['30j']['CM3'] = round($CM3*100)/100; //Cout moyen KWh
						}
					
					//EAU 30+30 JOURS	
						if ($this->statut < 5)
						{
							
							$Litres = 0;
							foreach($this->PLeau as $id=>$PL) 
							{	$Litres += $this->PLeau[$id]->histo->getQuantity('m3',30,strtotime("-1 month",$this->Date_c))*1000;
								//echo ($this->PLeau[$id]->histo->getQuantity('m3',30,strtotime("-1 month",$this->Date_c))*1000).'<br>';
							}
							
							$month = date("n",$this->Date_c)-1;
							if (($this->data->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
								$this->Ratio['eau']['30+30j']['L/hab/J'] = round($Litres*100 / (30 * $this->Occupants))/100;	
							}
							else{
								$this->Ratio['eau']['30+30j']['L/hab/J'] = round($Litres*100 / (30 * ($this->Occupants+$this->Occupants2)))/100;
							}
							
							$this->Ratio['eau']['30+30j']['m3'] = round($Litres/10)/100;
							
							$MF = 0; $CM3 = 0; 
							foreach($this->PLeau as $id=>$PL) 
							{
								$MF += $this->PLeau[$id]->histo->getQuantity('MF',30,null,30);
								$CM3 += $this->PLeau[$id]->histo->getQuantity('CM3',30,null,30)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['eau']['30+30j']['PS'][$id] = $this->PLeau[$id]->histo->getQuantity('PS',1,null,30);
							}
							
							$this->Ratio['eau']['30+30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['eau']['30+30j']['CM3'] = round($CM3*100)/100; //Cout moyen KWh
							
						}

					//EAU 365+30 JOURS	
						if ($this->statut < 5)
						{
							$Litres = 0;
							foreach($this->PLeau as $id=>$PL) 
								$Litres += $this->PLeau[$id]->histo->getQuantity('m3',30,null,365)*1000;
							
							$month = date("n",$this->Date_c);
							if (($this->data->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
								$this->Ratio['eau']['365+30j']['L/hab/J'] = round($Litres*100 / (30 * $this->Occupants))/100;	
							}
							else{
								$this->Ratio['eau']['365+30j']['L/hab/J'] = round($Litres*100 / (30 * ($this->Occupants+$this->Occupants2)))/100;
							}
							
							$this->Ratio['eau']['365+30j']['m3'] = round($Litres/10)/100;
							
							$MF = 0; $CM3 = 0; 
							foreach($this->PLeau as $id=>$PL) 
							{
								$MF += $this->PLeau[$id]->histo->getQuantity('MF',30,null,365);
								$CM3 += $this->PLeau[$id]->histo->getQuantity('CM3',30,null,365)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['eau']['365+30j']['PS'][$id] = $this->PLeau[$id]->histo->getQuantity('PS',1,null,365);
							}
							
							$this->Ratio['eau']['365+30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['eau']['365+30j']['CM3'] = round($CM3*100)/100; //Cout moyen KWh
						}
					
					//EAU 12 MOIS (365 jours)					
						$Litres = 0;
						foreach($this->PLeau as $id=>$PL) 
							$Litres += $this->PLeau[$id]->histo->getQuantity('m3',365)*1000*30/365;
						
						if ($this->data->idSiteType==1){ //Ecole, just count the administratifs from july to october
							$nb_occupants_moyen=(12*$this->Occupants+9*$this->Occupants2)/12;
							$this->Ratio['eau']['12m']['L/hab/J'] = round($Litres*100 / (30 * $nb_occupants_moyen))/100;
						}
						else{
							$this->Ratio['eau']['12m']['L/hab/J'] = round($Litres*100 / (30 * $this->Occupants+$this->Occupants2))/100;
						}
						
						$this->Ratio['eau']['12m']['m3'] = round($Litres/10)/100;
						
						if ($this->statut < 5)
						{
							$MF = 0; $CM3 = 0; 
							foreach($this->PLeau as $id=>$PL) 
							{
								$MF += $this->PLeau[$id]->histo->getQuantity('MF',365)*30/365;
								$CM3 += $this->PLeau[$id]->histo->getQuantity('CM3',365)/365;
								
								//Puissance souscrite
								$this->Ratio['eau']['12m']['PS'][$id] = $this->PLeau[$id]->histo->getQuantity('PS',1);
							}
							
							$this->Ratio['eau']['12m']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['eau']['12m']['CM3'] = round($CM3*100)/100; //Cout moyen KWh
						}
					
					//EAU Conso par mois sur 13 derniers mois
						//$now = strtotime("-1 month -1 day",mktime(12, 0, 0, date('m'), 1, date('Y')));
						//echo date('d/m/Y',$now);
						
						for ($dateC = $this->Date_c; $dateC >= strtotime("-13 month",$this->Date_c); $dateC = strtotime("-1 month",$dateC)) 
						{	
							//$date = strtotime(date('Y-m-',$dateC).date('t',$dateC)); //ensure we start from the last day of the month
							$date = $dateC;
							
							$m3 = 0;
							foreach($this->PLeau as $id=>$PL) 
								$m3 += $this->PLeau[$id]->histo->getQuantity('m3',30,$date);
							
							$v = ''; //if (isset($fake)) $v = ($fake)?'f':'r';
	
							$this->Ratio['eau']['mois'][$v.date('m/y',$date)]['m3'] = $m3;
						}
						//ordre chronologique
						$this->Ratio['eau']['mois'] = array_reverse($this->Ratio['eau']['mois'],true);
					/////////////////////////////////
					
					///////////ELEC
					//ELEC 30 JOURS : KWh/hab/J  et KWh/m²/J					
						$KWh = 0;
						//echo 'elec C<br>';
						foreach($this->PLelec as $id=>$PL) 
							$KWh += $this->PLelec[$id]->histo->getQuantity('KWh',30);
													
							$this->Ratio['elec']['30j']['KWh/J'] = round($KWh*100 / 30)/100;
							$this->Ratio['elec']['30j']['KWh/m²'] = round($KWh*100 / $this->Surface)/100;
							
							$month = date("n",$this->Date_c);
							if (($this->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
								$this->Ratio['elec']['30j']['KWh/hab'] = round($KWh*100 / $this->Occupants)/100;	
							}
							else{
								$this->Ratio['elec']['30j']['KWh/hab'] = round($KWh*100 / ($this->Occupants+$this->Occupants2))/100;
							}
						
						if ($this->statut < 5)
						{	
							$this->Ratio['elec']['30j']['PSt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['30j']['PSt'] += $PL->histo->getQuantity('PS',1);
							$this->Ratio['elec']['30j']['PAt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['30j']['PAt'] += $PL->histo->getQuantity('PA',1);
							
							$MF = 0; $CK = 0; 
							foreach($this->PLelec as $id=>$PL) 
							{
								$MF += $this->PLelec[$id]->histo->getQuantity('MF',30);
								$CK += $this->PLelec[$id]->histo->getQuantity('CK',30)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['elec']['30j']['PS'][$id] = $this->PLelec[$id]->histo->getQuantity('PS',1);
								$this->Ratio['elec']['30j']['PA'][$id] = $this->PLelec[$id]->histo->getQuantity('PA',1);
							}
							
							$this->Ratio['elec']['30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['elec']['30j']['CK'] = round($CK*100)/100; //Cout moyen KWh
						}
					 
					//ELEC Mois dernier (30+30J)
						if ($this->statut < 5)
						{
							//echo 'elec C-1<br>';
							$KWh = 0;
							foreach($this->PLelec as $id=>$PL) 
								$KWh += $this->PLelec[$id]->histo->getQuantity('KWh',30,strtotime("-1 month",$this->Date_c));
								
							//echo date('d-m-Y',strtotime("-1 month",$this->Date_c)).'-'.$KWh.'<br>';
							
							$this->Ratio['elec']['30+30j']['KWh/J'] = round($KWh*100 / 30)/100;							
							$this->Ratio['elec']['30+30j']['KWh/m²'] = round($KWh*100 / $this->Surface)/100;							
							
							$month = date("n",$this->Date_c)-1;							
							if (($this->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
								$this->Ratio['elec']['30+30j']['KWh/hab'] = round($KWh*100 / $this->Occupants)/100;
							}
							else{
								$this->Ratio['elec']['30+30j']['KWh/hab'] = round($KWh*100 / ($this->Occupants+$this->Occupants2))/100;
							}
							
							
							$this->Ratio['elec']['30+30j']['PSt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['30+30j']['PSt'] += $PL->histo->getQuantity('PS',1,strtotime("-1 month",$this->Date_c));
							$this->Ratio['elec']['30+30j']['PAt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['30+30j']['PAt'] += $PL->histo->getQuantity('PA',1,strtotime("-1 month",$this->Date_c));
							
							$MF = 0; $CK = 0; 
							foreach($this->PLelec as $id=>$PL) 
							{
								$MF += $this->PLelec[$id]->histo->getQuantity('MF',30,null,30);
								$CK += $this->PLelec[$id]->histo->getQuantity('CK',30,null,30)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['elec']['30+30j']['PS'][$id] = $this->PLelec[$id]->histo->getQuantity('PS',1,strtotime("-1 month",$this->Date_c));
								$this->Ratio['elec']['30+30j']['PA'][$id] = $this->PLelec[$id]->histo->getQuantity('PA',1,strtotime("-1 month",$this->Date_c));
							}
							
							$this->Ratio['elec']['30+30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['elec']['30+30j']['CK'] = round($CK*100)/100; //Cout moyen KWh
							
							//echo '-<br>';
						}

					//ELEC Mois dernier (365+30J)
						if ($this->statut < 5)
						{
							$KWh = 0;
							foreach($this->PLelec as $id=>$PL) 
								$KWh += $this->PLelec[$id]->histo->getQuantity('KWh',30,null,365);
								
							$this->Ratio['elec']['365+30j']['KWh/J'] = round($KWh*100 / 30)/100;
							$this->Ratio['elec']['365+30j']['KWh/m²'] = round($KWh*100 / $this->Surface)/100;
							$month = date("n",$this->Date_c);
							if (($this->idSiteType==1) and ($month>6) and ($month<10)){ //Ecole, just count the administratifs from july to october
								$this->Ratio['elec']['365+30j']['KWh/hab'] = round($KWh*100 / $this->Occupants)/100;	
							}
							else{
								$this->Ratio['elec']['365+30j']['KWh/hab'] = round($KWh*100 / ($this->Occupants+$this->Occupants2))/100;
							}
							$this->Ratio['elec']['365+30j']['PSt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['365+30j']['PSt'] += $PL->histo->getQuantity('PS',1,null,365);
							$this->Ratio['elec']['365+30j']['PAt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['365+30j']['PAt'] += $PL->histo->getQuantity('PA',1,null,365);
							
							
							$MF = 0; $CK = 0; 
							foreach($this->PLelec as $id=>$PL) 
							{
								$MF += $this->PLelec[$id]->histo->getQuantity('MF',30,null,365);
								$CK += $this->PLelec[$id]->histo->getQuantity('CK',30,null,365)/30;
								
								//Puissance souscrite / Puissance Atteinte
								$this->Ratio['elec']['365+30j']['PS'][$id] = $this->PLelec[$id]->histo->getQuantity('PS',1,null,365);
								$this->Ratio['elec']['365+30j']['PA'][$id] = $this->PLelec[$id]->histo->getQuantity('PA',1,null,365);
							}
							
							$this->Ratio['elec']['365+30j']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['elec']['365+30j']['CK'] = round($CK*100)/100; //Cout moyen KWh
						}
					 
					//ELEC Moyenne sur 1 mois des 12 MOIS (365 jours) : KWh/hab/J  et KWh/m²/J					
						$KWh = 0;
						foreach($this->PLelec as $id=>$PL) 
							$KWh += $this->PLelec[$id]->histo->getQuantity('KWh',365)*30/365; //moyenné sur 1 mois
							$this->Ratio['elec']['12m']['KWh/J'] = round($KWh*100 / 30)/100;
							$this->Ratio['elec']['12m']['KWh/m²'] = round($KWh*100 / $this->Surface)/100;
							if ($this->idSiteType==1){ //Ecole, just count the administratifs from july to october
								$nb_occupants_moyen=(12*$this->Occupants+9*$this->Occupants2)/12;
								$this->Ratio['elec']['12m']['KWh/hab'] = round($KWh*100 / $nb_occupants_moyen)/100;	
							}
							else{
								$this->Ratio['elec']['12m']['KWh/hab'] = round($KWh*100 / $this->Occupants)/100;
							}
							$this->Ratio['elec']['1an']['KWh']=round($KWh/30*365);
							//echo $this->Ratio['elec']['1an']['KWh'].'    '.$KWh;die;							
						if ($this->statut < 5)
						{
							$this->Ratio['elec']['12m']['PSt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['12m']['PSt'] += round($PL->histo->getQuantity('PS',365)/365);
							$this->Ratio['elec']['12m']['PAt'] = 0;
							foreach($this->PLelec as $id=>$PL) $this->Ratio['elec']['12m']['PAt'] += round($PL->histo->getQuantity('PA',365)/365);
							
							$MF = 0; $CK = 0; 
							foreach($this->PLelec as $id=>$PL) 
							{
								$MF += $this->PLelec[$id]->histo->getQuantity('MF',365)*30/365;
								$CK += $this->PLelec[$id]->histo->getQuantity('CK',365)/365;
							}
							$this->Ratio['elec']['12m']['MFm'] = round($MF/10000)/100; //Montant mensuel Facture
							$this->Ratio['elec']['12m']['CK'] = round($CK*100)/100; //Cout moyen KWh
						}
						
						
					//ELEC Conso par mois sur 13 derniers mois
					//Conso par mois sur les 13 derniers mois
						//$now = strtotime("-1 month -1 day",mktime(12, 0, 0, date('m'), 1, date('Y')));
						
						for ($dateC = $this->Date_c; $dateC >= strtotime("-13 month",$this->Date_c); $dateC = strtotime("-1 month",$dateC))
						{	
							//echo date('d-m-Y',$dateC).'<br>';
							$date = $dateC;
							//$date = strtotime(date('Y-m-',$dateC).'28'); //le 28 du mois
							
							$KWhBT = 0; $KWhMTHP = 0; $KWhMTP = 0;
							foreach($this->PLelec as $id=>$PL) 
							{
								//if ($id == 44) $fake = $this->PLelec[$id]->histo->calendar[$date]['Fake'];
								$KWhBT += $this->PLelec[$id]->histo->getQuantity('MWhBT',30,$date);
								$KWhMTHP += $this->PLelec[$id]->histo->getQuantity('MWhMTHP',30,$date);
								$KWhMTP += $this->PLelec[$id]->histo->getQuantity('MWhMTP',30,$date);
							}
							
							$v = '';
							//if (isset($fake)) $v = ($fake)?'f':'r';
							$this->Ratio['elec']['mois'][$v.date('m/y',$date)]['MWhBT'] = $KWhBT;
							$this->Ratio['elec']['mois'][$v.date('m/y',$date)]['MWhMTHP'] = $KWhMTHP;
							$this->Ratio['elec']['mois'][$v.date('m/y',$date)]['MWhMTP'] = $KWhMTP;
						}
						//ordre chronologique
						$this->Ratio['elec']['mois'] = array_reverse($this->Ratio['elec']['mois'],true);
					}	
					
				}
			}
		}
		if ($this->statut < 5) $this->make_inputs();
	}
	
	//RECHARGE LE SITE
	function reload()
	{
		$this->editing(false);
		list($a1,$a2,$a3,$a4) = $this->reload_ARGS;
		if ($a2 == 2) $a2 = false;
		$this->get($a1,$a2,$a3,$a4);
		
	}
	
	//NOMBRE DE JOUR AVANT AUJOURDHUI : TAILLE DE L'HISTORIQUE
	function setHistoSize($n)
	{
		$this->histoSize = $n;
	}
	
	//PASSE LE SITE EN EDITION
	function edit()
	{
		$this->get($this->id(),2);
		$this->editing(true);
	}
	
	//NE GARDE QUE LE PL EN EDITION
	function edit_PL($type,$idPL)
	{
		if (isset($this->{'PL'.$type}[$idPL]))
		{
			$PLED = $this->{'PL'.$type}[$idPL];
			foreach($this->PLtypes as $ty) unset ($this->{'PL'.$ty});
			$this->{'PL'.$type}[$idPL] = $PLED;
			$this->{'PL'.$type}[$idPL]->edit();
			$this->onWork['PL'] = $idPL;
		}
	}
	
	//LISTE DES POINTS DE LIVRAISON
	function build_PL($type,$newPL=false,$days_histo = 495)
	{
		$this->PLtypes[] = $type;
		
		$this->{'PL'.$type} = array();
			
		//RETRIEVE LIST
		$this->db->select('idSite_PL_'.$type);
		$this->db->where(array('idSite' => $this->id(), 'DateFin IS NULL' => null));
		$query = $this->db->get('Site_PL_'.$type);
		
		if ($query->num_rows() > 0)
			foreach ($query->result() as $row)
			{
				if ($type == 'elec') $this->PLelec[$row->idSite_PL_elec] = new PLelec_model($this->id(),$row->idSite_PL_elec,$this->statut,$days_histo,$this->Date_c);
				else if ($type == 'eau') $this->PLeau[$row->idSite_PL_eau] = new PLeau_model($this->id(),$row->idSite_PL_eau,$this->statut,$days_histo,$this->Date_c);
				else if ($type == 'gasoil') $this->PLgasoil[$row->idSite_PL_gasoil] = new PLgasoil_model($this->id(),$row->idSite_PL_gasoil,$this->statut,$days_histo,$this->Date_c);
			}
			
				
			
		if ($newPL) 
		{
			if ($type == 'elec') $this->PLelec['new'] = new PLelec_model($this->id(),false,$this->statut);
			else if ($type == 'eau') $this->PLeau['new'] = new PLeau_model($this->id(),false,$this->statut);
			else if ($type == 'gasoil') $this->PLgasoil['new'] = new PLgasoil_model($this->id(),false,$this->statut);
		}
		
		foreach($this->{'PL'.$type} as $id=>$pl) $this->{'PL'.$type}[$id]->formfields();
	}
	
	//LISTE DES BATIMENTS
	function build_Batiments($new=false)
	{
		$this->Batiments = array();
		
		$this->Bat_Problems = array(
									'Pieces' => '',
									'Eclairages' => ''
									);
		
		//RETRIEVE LIST
		$this->db->select('idBatiment, Nom');
		$this->db->where(array('idSite' => $this->id(), 'DateFin IS NULL' => null));
		$query = $this->db->get('Batiment');
		
		if ($query->num_rows() > 0)
			foreach ($query->result() as $row) 
			{
				$i='';$j=1; 
				while(isset($this->Batiments[$row->Nom.$i])) {$j++;$i=' '.$j;}
				if($j>1) $row->Nom = $row->Nom.$i; 
				
				$this->Batiments[$row->idBatiment] = $row->Nom;
				
								
				$this->Bats[$row->idBatiment] = new Batiment_model();
				$this->Bats[$row->idBatiment]->get($row->idBatiment,$this->statut);
					
				$this->Bats[$row->idBatiment]->Bat_Occupation->formfields();
				$this->Bats[$row->idBatiment]->Bat_Technique->formfields();
				$this->Bats[$row->idBatiment]->Bat_Equip_Air->formfields();
				$this->Bats[$row->idBatiment]->formfields();
							
				for($i=1;$i<=$this->Bats[$row->idBatiment]->data->NiveauNbr;$i++) 
				{
					$this->Bats[$row->idBatiment]->Bat_Niveaux[$i]->formfields();
				
					//test absence de piece sur un niveau
					if ($this->Bats[$row->idBatiment]->Bat_Niveaux_pieces[$i] == 0)
					{
						if ($this->Bat_Problems['Pieces'] == '') $this->Bat_Problems['Pieces'] = 'Absence de pièces au :<br />';
						$this->Bat_Problems['Pieces'] .= 'Batiment : '.$this->Bats[$row->idBatiment]->data->Nom.' , Niveau : '.$i.';<br />';
					}
				
					//test absence d'eclairage sur un niveau
					if ($this->Bats[$row->idBatiment]->Bat_Niveaux_eclairages[$i] == 0)
					{
						if ($this->Bat_Problems['Eclairages'] == '') $this->Bat_Problems['Eclairages'] = 'Absence d\'éclairage au <br />';
						$this->Bat_Problems['Eclairages'] .= 'Batiment : '.$this->Bats[$row->idBatiment]->data->Nom.' , Niveau : '.$i.';<br />';
					}
				}
			}
			//echo $this->Bat_Problems['Pieces'];
			
		if ($new)
			$this->Batiments['new'] = '<em>Nouveau</em>';
	}
	
	//LISTE DES ALERTES
	function build_Alertes()
	{
		$this->Alerte = array();
		//CREATE NEW ALERT
		//setlocale (LC_TIME, 'fr_FR','fra');
		//$mois = strftime("%B", time()-30*24*3600);
		$tableaumois = array("","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
		$mois = $tableaumois[date("n",$this->Date_c)].' '.date("Y",$this->Date_c);
		$Alertetemp = null;
		
		//Conditions d'affichage des alertes
		////ELEC
		//type 1 : augmentation des consommations
		if ($this->Ratio['elec']['30j']['MFm']>(1.05*$this->Ratio['elec']['30+30j']['MFm']))
		{
			$hausse=round(10*(($this->Ratio['elec']['30j']['MFm']/$this->Ratio['elec']['30+30j']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport au mois précédent';
			$Duree_validite = 1;
			$TypeAlerte=1;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, 1, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		if ($this->Ratio['elec']['30j']['MFm']>(1.05*$this->Ratio['elec']['365+30j']['MFm']))
		{
			$hausse=round(10*(($this->Ratio['elec']['30j']['MFm']/$this->Ratio['elec']['365+30j']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport au même mois de l\'année précédente';
			$Duree_validite = 12;
			$TypeAlerte=1;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		if ($this->Ratio['elec']['30j']['MFm']>(1.05*$this->Ratio['elec']['12m']['MFm']))
		{
			$newAlerte=true;
			$hausse=round(10*(($this->Ratio['elec']['30j']['MFm']/$this->Ratio['elec']['12m']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport à la moyenne annuelle';
			$Duree_validite = 12;
			$TypeAlerte=1;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		//type 2 : augmentation du cout du kWh
		if ($this->Ratio['elec']['30j']['CK']>(1.05*$this->Ratio['elec']['30+30j']['CK']))
		{
			$newAlerte=true;
			$hausse=round(10*(($this->Ratio['elec']['30j']['CK']/$this->Ratio['elec']['30+30j']['CK'])-1)*100)/10;
			$info='Au mois de '.$mois.' le coût du kWh a augmenté de '.$hausse.'% par rapport au mois précédent';
			$Duree_validite = 1;
			$TypeAlerte=2;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		if ($this->Ratio['elec']['30j']['CK']>(1.05*$this->Ratio['elec']['365+30j']['CK']))
		{
			$hausse=round(10*(($this->Ratio['elec']['30j']['CK']/$this->Ratio['elec']['365+30j']['CK'])-1)*100)/10;
			$info='Au mois de '.$mois.' le coût du kWh a augmenté de '.$hausse.'% par rapport au même mois de l\'année précédente';
			$Duree_validite = 12;
			$TypeAlerte=2;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
			}
		if ($this->Ratio['elec']['30j']['CK']>(1.05*$this->Ratio['elec']['12m']['CK']))
		{
			$hausse=round(10*(($this->Ratio['elec']['30j']['CK']/$this->Ratio['elec']['12m']['CK'])-1)*100)/10;
			$info='Au mois de '.$mois.' le coût du kWh a augmenté de '.$hausse.'% par rapport à la moyenne annuelle';
			$Duree_validite = 12;
			$TypeAlerte=2;
			$flux='elec';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}	
		
		//type 3 : dépassement de puissance (puissance atteinte>souscrite)
		foreach($this->PLelec as $id=>$PL) 
			{
				if ($this->Ratio['elec']['30j']['PA'][$id] >$this->Ratio['elec']['30j']['PS'][$id])
				{
					$hausse = round(10*($this->Ratio['elec']['30j']['PA'][$id])-($this->Ratio['elec']['30j']['PS'][$id]))/10;
					$info='Au mois de '.$mois.' la puissance atteinte a dépassé de '.$hausse.'kW la puissance souscrite';
					$Duree_validite = 1;
					$TypeAlerte=3;
					$flux='elec';
					$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
				}				
			}
		
		//type 4 : Modification de la puissance souscrite
		foreach($this->PLelec as $id=>$PL) 
			{
				if ($this->Ratio['elec']['30j']['PS'][$id]>$this->Ratio['elec']['30+30j']['PS'][$id])
				{
					$hausse = round(10*$this->Ratio['elec']['30j']['PS'][$id]-$this->Ratio['elec']['30+30j']['PS'][$id])/10;
					$info='Au mois de '.$mois.' la puissance souscrite a augmenté de '.$hausse.'kWh';
					$Duree_validite = 1;
					$TypeAlerte=4;
					$flux='elec';
					$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
				}
				if ($this->Ratio['elec']['30j']['PS'][$id]<$this->Ratio['elec']['30+30j']['PS'][$id])
				{
					$hausse = -round(10*($this->Ratio['elec']['30j']['PS'][$id]-$this->Ratio['elec']['30+30j']['PS'][$id]))/10;
					$info='Au mois de '.$mois.' la puissance souscrite a diminué de '.$hausse.'kWh';
					$Duree_validite = 1;
					$TypeAlerte=4;
					$flux='elec';
					$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
				}				
			}
		//type 5 : Puissance atteinte > 0,9 puissance transfo
		foreach($this->PLelec as $id=>$PL) 
		{
			$val[$id]=$PL->diag();
			if (isset($val[$id]['Patteinte'])and (isset($val[$id]['Ptransfo'])) and ($val[$id]['Ptransfo']!=0))
			{
				if ($val[$id]['Patteinte']>0.9*$val[$id]['Ptransfo'])
					{
						$hausse = round(100*$val[$id]['Patteinte']/$val[$id]['Ptransfo']);
						$info='Au mois de '.$mois.' la puissance appelée a atteint '.$hausse.'% de la puissance du transformateur.';
						$Duree_validite = 1;
						$TypeAlerte=5;
						$flux='elec';
						$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
					}
			}
		}
		//type 6 : Puissance atteinte > puissance souscrite
		foreach($this->PLelec as $id=>$PL) 
		{
			$val[$id]=$PL->diag();
			if (isset($val[$id]['Patteinte'])and ($val[$id]['Psouscrite']!=0))
			{
				if ($val[$id]['Patteinte']>$val[$id]['Psouscrite'])
					{
						$hausse = round(100*$val[$id]['Patteinte']/$val[$id]['Psouscrite'])-100;
						$info='Au mois de '.$mois.' la puissance appelée a dépassé de '.$hausse.'% la puissance souscrite.';
						$Duree_validite = 1;
						$TypeAlerte=6;
						$flux='elec';
						$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
					}
			}
		}
		/////EAU
		//type 1 : augmentation des consommations
		if ($this->Ratio['eau']['30j']['MFm']>(1.05*$this->Ratio['eau']['30+30j']['MFm']))
		{
			$hausse=round(10*(($this->Ratio['eau']['30j']['MFm']/$this->Ratio['eau']['30+30j']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport au mois précédent';
			$Duree_validite = 1;
			$TypeAlerte=1;
			$flux='eau';
			$Alertetemp[]= new Alerte_model(null, 1, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		if ($this->Ratio['eau']['30j']['MFm']>(1.05*$this->Ratio['eau']['365+30j']['MFm']))
		{
			$hausse=round(10*(($this->Ratio['eau']['30j']['MFm']/$this->Ratio['eau']['365+30j']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport au même mois de l\'année précédente';
			$Duree_validite = 12;
			$TypeAlerte=1;
			$flux='eau';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		if ($this->Ratio['eau']['30j']['MFm']>(1.05*$this->Ratio['eau']['12m']['MFm']))
		{
			$newAlerte=true;
			$hausse=round(10*(($this->Ratio['eau']['30j']['MFm']/$this->Ratio['eau']['12m']['MFm'])-1)*100)/10;
			$info='Au mois de '.$mois.' les consommations ont augmenté de '.$hausse.'% par rapport à la moyenne annuelle';
			$Duree_validite = 12;
			$TypeAlerte=1;
			$flux='eau';
			$Alertetemp[]= new Alerte_model(null, $TypeAlerte, 1, null, $info, $this->id(), $Duree_validite, $flux);
		}
		
		
		////SAUVEGARDE DES ALERTES DANS L'HISTORIQUE DES ALERTES
		//Verifier que cette alerte n'est pas deja emise et encore dans sa periode de validite
		if (is_array($Alertetemp))
		{
			foreach($Alertetemp as $AT)
			{
				
				$this->db->select('Date');
				$this->db->where(array('idSite' => $this->id(), 'idAlerteType' => $AT->type, 'Flux' => $AT->flux));
				$query = $this->db->get('Alerte');
			
				$Alerte_already_done=false;
				if (($query->num_rows()) > 0)	
					foreach ($query->result() as $row) 
						if (now() < (strtotime('+'.$AT->validite.' month',(strtotime($row->Date))))) $Alerte_already_done=true;
	

				if (!$Alerte_already_done) $AT->save();			
			}
		}
		
		//RETRIEVE LIST
		$this->db->select('idAlerte, idAlerteType, Flux, Etat, idAlerteParent, Commentaire');
		//$this->db->where('idSite',$this->id());
		$this->db->where('idSite',$this->id());
		$this->db->order_by("idAlerte", "desc"); 
		$query = $this->db->get('Alerte');
		
		$this->Alerte['RougeAlerts'] = '';
		$this->Alerte['OrangeAlerts'] = '';
		if (($query->num_rows() > 0) and ($this->statut >= 4))
		{	
			
			foreach ($query->result() as $row) 
			{					
				$this->Alerte[$row->Flux][$row->idAlerteType][$row->idAlerte] = new Alerte_model($row->idAlerte, null, null, null, null, $this->id(), null, $row->Flux);
				
				//Déterminer si une des alertes rouges est active, donc qu'elle n'a pas d'alerte enfant associée
				if ($row->Etat == 1)
				{
					$red = $row;
					$orange=false;
					foreach ($query->result() as $rowenfant)
						if (($rowenfant->idAlerteParent)==($red->idAlerte))
						{
							$red=false;
							$orange = $rowenfant;
								foreach ($query->result() as $rowenfant2) 
									if (($rowenfant2->idAlerteParent)==($orange->idAlerte)) $orange=false;
						}
						
					if ($red) $this->Alerte['RougeAlerts'] .= strtoupper($red->Flux).': '.$red->Commentaire.'<br />';
					if ($orange) $this->Alerte['OrangeAlerts'] .= strtoupper($orange->Flux).': '.$orange->Commentaire.'<br />';
				}
			}
		}			
	}
	
	//LISTE DES EQUIPEMENTS
	function build_Equipements()
	{
		$this->Equipement['Chaudiere'] = 0;
		$this->Equipement['Groupe_elec'] = 0;
		$this->Equipement['Groupe_froid'] = 0;
		
		//CHAUDIERE ET GROUPE ELECTROGENE
		if (is_array($this->PLgasoil))
			foreach($this->PLgasoil as $pl)
			{
				$this->Equipement['Groupe_elec'] += $pl->Equipement['Groupe_elec'];
				$this->Equipement['Chaudiere'] += $pl->Equipement['Chaudiere'];
			}
		
		//GROUPE FROID
		if (is_array($this->Bats))
			foreach($this->Bats as $bat) $this->Equipement['Groupe_froid'] += $bat->Bat_Equip_Air->Equipement['Groupe_froid'];
	}
	
	//CREATION DES CHAMPS ET INFOS
	function make_inputs()
	{		
		$this->form['idMinistere']->set('Ministère','id_select_E')->add_rule('required');
		$this->form['Nom']->set('Nom','text',30)->add_rule('required');
		$this->form['Adresse1']->set('Adresse','text',30)->add_rule('required');
		$this->form['Adresse2']->set('Complement d\'adresse','text',30);
		$this->form['idVille']->set('Ville','id_select')->add_rule('required');
		$this->form['ContactRespNom']->set('Nom du Responsable','text',20);
		$this->form['ContactRespTel']->set('Telephone du Responsable','text',12);
		$this->form['ContactRespFonc']->set('Fonction du Responsable','text',12);		
		$this->form['ContactTechNom']->set('Nom du Contact 1','text',20);
		$this->form['ContactTechTel']->set('Tel du Contact 1','text',12);
		$this->form['ContactTechFonc']->set('Fonction du Contact 1','text',12);		
		$this->form['ContactSup2Nom']->set('Nom du Contact 2','text',20);
		$this->form['ContactSup2Tel']->set('Tel du Contact 2','text',12);
		$this->form['ContactSup2Fonc']->set('Fonction du Contact 2','text',12);		
		$this->form['ContactSup3Nom']->set('Nom du Contact 3','text',20);
		$this->form['ContactSup3Tel']->set('Tel du Contact 3','text',12);
		$this->form['ContactSup3Fonc']->set('Fonction du Contact 3','text',12);		
		$this->form['idEquipe']->set('Equipe','id_select_E')->add_rule('required');
		$this->form['DateVisite']->set('Date de Visite','date')->add_rule('date');
		$this->form['DatePrevue']->set('Date prevue de la visite','date');
		$this->form['idSiteType']->set('Type de site','id_select_E');
		$this->form['BatimentNbr']->set('Nombre de Batiments','text',5)->add_rule('integer');
		$this->form['Commentaire']->set('Observations','textarea',5,60);
		$this->form['Photo']->set('Photo','check');
		$this->form['Plan']->set('Plan','check');
				
		$this->add_form('SubmitBTN')->set('Enregistrer','submit','save_site');
		$this->add_form('EditBTN')->set('Modifier le Site','submit','edit_site');	
		$this->add_form('ValidBTN')->set('Valider le Site','submit','valid_site');
		$this->add_form('UnValidBTN')->set('Repasser en mode recensement','submit','unvalid_site');
		$this->add_form('DeleteBTN')->set('Supprimer','submit','delete_site','Supprimer ce Site ?');	
		
		if (($this->statut <= 1)&&(!$this->byPass_statut1))
		{
			//DEFAULT VALUES :
			$this->setdata('DateCreation',date('Y-m-d'));
			if ($this->statut == 0) 
			{
				if ($this->simplelogin->user() == 'ctee1') $this->setformdata('idEquipe',1);
				if ($this->simplelogin->user() == 'ctee2') $this->setformdata('idEquipe',2);
			}
			
			//DISPLAY PARAMETERS
			$display = array(
						'idMinistere'	=> 'field',
						'Nom' 		=> 'field',
						'Adresse1' 	=> 'field',
						'idVille' 	=> 'field',
						'idEquipe' 	=> 'field',
						'DatePrevue' 	=> 'field',
						'ContactRespNom'	=> 'field',
						'ContactRespTel' 	=> 'field',
						'ContactRespFonc' 	=> 'field',
						'ContactTechNom' 	=> 'field',
						'ContactTechTel' 	=> 'field',
						'ContactTechFonc' 	=> 'field',
						'ContactSup2Nom' 	=> 'field',
						'ContactSup2Tel' 	=> 'field',
						'ContactSup2Fonc' 	=> 'field',
						'ContactSup3Nom' 	=> 'field',
						'ContactSup3Tel' 	=> 'field',
						'ContactSup3Fonc' 	=> 'field',
						
						'SubmitBTN' 	=> 'field'
					);
			
			$this->fields_method($display);
			
		}
		else if ($this->statut <= 2)
		{
			//DISPLAY PARAMETERS
			$display = array(
						'idMinistere'	=> 'display',
						'Nom' 		=> 'field',
						'Adresse1' 	=> 'display',
						'Adresse2' 	=> 'field',
						'idVille' 	=> 'display',
						'idEquipe' 	=> 'display',
						'ContactRespNom'	=> 'field',
						'ContactRespTel' 	=> 'field',
						'ContactRespFonc' 	=> 'field',
						'ContactTechNom' 	=> 'field',
						'ContactTechTel' 	=> 'field',
						'ContactTechFonc' 	=> 'field',
						'ContactSup2Nom' 	=> 'field',
						'ContactSup2Tel' 	=> 'field',
						'ContactSup2Fonc' 	=> 'field',
						'ContactSup3Nom' 	=> 'field',
						'ContactSup3Tel' 	=> 'field',
						'ContactSup3Fonc' 	=> 'field',
						'BatimentNbr' 	=> 'field',
						'DatePrevue'	=> 'display',
						'DateVisite'	=> 'field',
						'idSiteType'	=> 'field',
						'Commentaire'	=> 'field',
						'Photo'		=> 'field',
						'Plan'		=> 'field',
						'SubmitBTN' 	=> 'field'
					);
			
			if (($this->simplelogin->group() >= 3)||($this->byPass_statut1)) 
			{	
				$display['idEquipe'] = 'field';
				 if ($this->statut > 0) $display['DeleteBTN'] = 'field';
				$display['Adresse1'] = 'field';
				$display['idMinistere'] = 'field';
				$display['DatePrevue'] = 'field';
			
				if ($this->byPass_statut1)
				{
					$display['DatePrevue'] = 'hide';
					$display['Nom'] = 'field';
					$display['idVille'] = 'field';
				}
			}
					
			$this->fields_method($display);
		}
		else if ($this->statut >= 3)
		{			
			$this->fields_method('display');
			$display = array(
						'EditBTN' 	=> 'hide',
						'ValidBTN' 	=> 'hide',
						'UnValidBTN' 	=> 'hide',
						'DeleteBTN' 	=> 'hide',
						'SubmitBTN' 	=> 'hide',
					);
			
			if ($this->data->ValideManu != 1) 
			{
				$display['ValidBTN'] = 'field';
				$display['EditBTN'] = 'field';
			}
			
			if ($this->simplelogin->group() >= 3)  
			{
				if ($this->simplelogin->group() > 3) $display['DeleteBTN'] = 'field';
				if ($this->data->ValideManu == 1) $display['UnValidBTN'] = 'field'; 
			}
			else if ((strtotime($this->data->ValideDate) >= strtotime('-15 days',time()))&&($this->data->ValideManu == 1)) $display['UnValidBTN'] = 'field'; 
					
			$this->fields_method($display);
		}
	}
	
	//TRAITEMENT DES ACTIONS
	function process() //get & execute action !
	{ 
		//process des alertes
		$tableau_flux=array('elec','eau');
		if ($idAlerte= $this->input->post('idAlerte'))
		{	
			if($flux=$this->input->post('Flux'))	
			{
				foreach ($tableau_flux as $flux)
				{	
					if (isset($this->Alerte[$flux]))
					{	
						foreach ($this->Alerte[$flux] as $idTypeAlerte=>$liste) 
						{
							if (isset($liste[$idAlerte])) 
							{
								//$flux=$this->Alerte[$idTypeAlerte][$idAlerte];
								$this->Alerte[$flux][$idTypeAlerte][$idAlerte]->process($idTypeAlerte,$this->id(),$flux);
							}
						}
					}
				}
			}	
		}	
		
		switch($this->action)
		{
			case 'valid_site':
				include_once('batiment_model.php');
				$unvalid= false;
				$batNBR = 0;
				$PLelecNBR = 0;
				$PLeauNBR = 0;
				foreach($this->Batiments as $idB=>$name)
				{
					if ($idB != 'new'):
						$batNBR++;
						$bat = new Batiment_model();
						$bat->get($idB);
						if ($bat->statut < 3) 
						{
							$this->session->set_userdata('error', 'Vous devez terminer la saisie pour le bâtiment "'.$name.'"');
							unset($bat);
							$unvalid = true;
						}
						unset($bat);
					endif;
				}
				if ($batNBR < $this->data->BatimentNbr) 
				{
					$this->session->set_userdata('error', 'La fiche Site indique '.$this->data->BatimentNbr.' bâtiments, mais seulement '.$batNBR.' ont été créés: vous devez saisir tous les Bâtiments du Site avant de le valider !');
					$unvalid = true;
				}
				else if ($batNBR > $this->data->BatimentNbr) 
				{
					$this->session->set_userdata('error', 'La fiche Site indique '.$this->data->BatimentNbr.' bâtiments, mais '.$batNBR.' ont été créés: vous avez saisi plus de Bâtiments que le nombre déclaré initialement ! 
						Vous devez changer le nombre de bâtiments dans la déclaration du site (1er cadre) avant de pouvoir valider.');
					$unvalid = true;
				}
				
				foreach($this->PLelec as $idP=>$PL) if ($idP != 'new') $PLelecNBR++;
				if ($PLelecNBR == 0) 
				{
					$this->session->set_userdata('error', 'Vous devez saisir au moins un Point de Livraison Electrique pour valider le Site !');
					$unvalid = true;
				}
				foreach($this->PLeau as $idP=>$PL) if ($idP != 'new') $PLeauNBR++;
				//if ($PLeauNBR == 0) 
				if(false)
				{
					$this->session->set_userdata('error', 'Vous devez saisir au moins un Point de Livraison Eau pour valider le Site !');
					$unvalid = true;
				}
				if ($unvalid) break;
			case 'unvalid_site':
				$this->editing(false);
			case 'save_site':
				echo 'save_site';
				if (($this->wantedID !== 'new')&&($this->editing())) $this->edit();
				
				if ($this->checkfields())
				{				
					if ($this->action == 'valid_site') {$this->setdata('ValideManu',1); $this->setdata('ValideDate',date("Y-m-d"));}
					else if ($this->action == 'unvalid_site') {$this->setdata('ValideManu',null); $this->setdata('ValideDate',null);}
					
					$this->save();
					if ((($this->statut == 0)&&(!$this->byPass_statut1))||($this->action == 'valid_site'))
					{
						redirect(str_replace('/site/'.$this->wantedID,'/site/created',uri_string())); //nouveau record : on retourne à l'acceuil
						//redirect(str_replace($this->wantedID,$this->id(),uri_string())); //continue record
					} 
					else if (($this->action == 'unvalid_site')||(($this->statut == 0)&&($this->byPass_statut1))) redirect('recensement/site/'.$this->id());
					else redirect(uri_string());
				}
				//$this->build_Batiments();
				return true;
			break;
			
			case 'edit_site':
				if ($this->guard('Update')) $this->edit();
				return true;
			break;
			
			case 'delete_site':
				$id = $this->id();
				$this->remove();
				redirect($this->uri->segment(1));
			break;
			
			case 'delete_PL_elec':
			case 'delete_PL_eau':
			case 'delete_PL_gasoil':
				list(,,$type) = explode('_',$this->action);
				$idPL = $this->input->post('idSite_PL_'.$type);
				
				if (isset($this->{'PL'.$type}[$idPL])) $this->{'PL'.$type}[$idPL]->remove();
				else $this->session->set_userdata('info', 'Impossible de supprimer ce Point de Livraison !');
				
				$this->reload();
				return true;
			break;
			
			case 'save_PL_elec':
			case 'save_PL_eau':				
			case 'save_PL_gasoil':
				list(,,$type) = explode('_',$this->action);
				
				if (!($idPL = $this->input->post('idSite_PL_'.$type))) $idPL = 'new';
				else if ($this->editing()) $this->edit_PL($type,$idPL); //Charge le PL concerné en EDIT MODE.
				
				if ($this->{'PL'.$type}[$idPL]->checkfields())
				{	
					
					/*if ($this->action == 'save_PL_eau') $cat = 'BT';
					else if ($this->action == 'save_PL_gasoil') $cat = 'GASOIL';
					else if ($this->input->post('idTension') == 1) $cat = 'BT';
					else if ($this->input->post('idTension') >= 2) $cat = 'MT';
					*/
					
					if ($this->action == 'save_PL_eau') $cat = 'eaux';
					else if ($this->action == 'save_PL_gasoil') $cat = 'GASOIL';
					else if ($this->input->post('idTension') == 1) $cat = 'bts';
					else if ($this->input->post('idTension') >= 2) $cat = 'mts';
					
					
					//recupere NoPL ou NoCompteur
					$No = trim($this->input->post('NoPL'));
					if ($No != '') $fControl = 'Point_de_livraison';
					else 
					{
						$No = trim($this->input->post('NoCompteur'));
						$fControl = 'No_compteur';
						if ($No != '')
						{
							if ($type == 'eau') $No = 'O'.$No;
							else if ($type == 'elec') $No = 'E'.$No;
						}
					}
					
					//check if NoPL ou NoCompteur is valid !
					if (($No != '')&&($type != 'gasoil'))
					{
						$tooMany = false;
						$cat2 = null;
						
						$query = $this->EwatchDB->query("SELECT COUNT(*) AS `numrows` FROM (`facture".$cat."`) WHERE `".$fControl."` LIKE '".$No."'");
						if ($query->row()->numrows == 0) 
						{
							$badNo = true;
							if ($cat == 'eaux') $cat2 = 'bts';
							if ($cat == 'bts') $cat2 = 'mts';
							if ($cat == 'mts') $cat2 = 'eaux';
							
							if ($cat2 !=  '')
							{
								$query = $this->EwatchDB->query("SELECT COUNT(*) AS `numrows` FROM (`facture".$cat2."`) WHERE `".$fControl."` LIKE '".$No."'");
								if ($query->row()->numrows > 0) $badNo = false;
								else if ($query->row()->numrows > 1) $tooMany = true;
							}
							
							if ($badNo)
							{
								if ($cat == 'eaux') $cat2 = 'bts';
								if ($cat == 'bts') $cat2 = 'mts';
								if ($cat == 'mts') $cat2 = 'eaux';
							
								if ($cat2 !=  '')
								{
									$query = $this->EwatchDB->query("SELECT COUNT(*) AS `numrows` FROM (`facture".$cat2."`) WHERE `".$fControl."` LIKE '".$No."'");
									if ($query->row()->numrows > 0) $badNo = false;
									else if ($query->row()->numrows > 1) $tooMany = true;
								}
							}
			
							
							
							if ($badNo)
							{
								$this->session->set_userdata('error',$fControl.' introuvable dans la base SEEG...
									<SCRIPT language="Javascript">damn = "Le '.$fControl.' saisi est introuvable dans la base de données SEEG !\n\nVerifiez le '.$fControl.' et si cela ne fonctionne toujours pas,\ncontactez un administrateur pour qu\'il fasse l\'ajout manuellement.";</SCRIPT>');
								return false;
							}
						}
						else if ($query->row()->numrows > 1) $tooMany = true;
						
						if (($tooMany)&&(false))
						{
							$this->session->set_userdata('error','Plusieurs PL pour ce numéro, verifiez la saisie !
									<SCRIPT language="Javascript">damn = "Plusieurs PL correspondent au numéro saisi !\n\nVerifiez le numero saisi. Si cela ne fonctionne pas, signalez ce problème à un administrateur.";</SCRIPT>');
							
							return false;
						}
						
						//check if not existing NoCompteur!
						$No = trim($this->input->post('NoCompteur'));
						if (($No!='')and($idPL == 'new'))
						{
							$query = $this->db->query("SELECT COUNT(*) AS `numrows` FROM (`Site_PL_".$type."`) WHERE `NoCompteur` LIKE '".$No."' AND `DateFin` IS NULL");
							if ($query->row()->numrows > 0) 
							{
								$this->session->set_userdata('error','Numero de compteur déjà saisi dans un Site! Vous ne pouvez pas saisir plusieurs fois le même compteur...
									<SCRIPT language="Javascript">damn = "Le numero de compteur saisi a déjà été enregistré !\n\nVerifiez le numéro de compteur et si cela ne fonctionne toujours pas,\ncontactez un administrateur.";</SCRIPT>');
								return false;
							}
						}
						
						//check if not existing NoPL!
						$No = trim($this->input->post('NoPL'));
						if (($No!='')and($idPL == 'new'))
						{
							$query = $this->db->query("SELECT COUNT(*) AS `numrows` FROM (`Site_PL_".$type."`) WHERE `NoPL` LIKE '".$No."' AND `DateFin` IS NULL");
							if ($query->row()->numrows > 0) 
							{
								$this->session->set_userdata('error','Numero de PL déjà saisi dans un Site! Vous ne pouvez pas saisir plusieurs fois le même PL...
									<SCRIPT language="Javascript">damn = "Le numero de PL saisi a déjà été enregistré !\n\nVerifiez le numéro de compteur et si cela ne fonctionne toujours pas,\ncontactez un administrateur.";</SCRIPT>');
								return false;
							}
						}
					}
					
					//Save & redirect
					$this->{'PL'.$type}[$idPL]->save();
					redirect(uri_string());
					return true;
				}
				return false;
			break;
			
			case 'edit_PL_elec':
			case 'edit_PL_eau':
			case 'edit_PL_gasoil':
				list(,,$type) = explode('_',$this->action);
				$idPL = $this->input->post('idSite_PL_'.$type);
				
				if (isset($this->{'PL'.$type}[$idPL])) $this->edit_PL($type,$idPL);
				return true;
			break;
			
			default: return false;
		}
	}
	
	//AJOUT DE REGLE AVANT VALIDATION
	function mix_down()
	{
		if ((($this->statut >= 2)&&(($_POST['DateVisite'] != '')||(!is_null($this->data->DateVisite))))||($this->byPass_statut1))
		{
			$this->form['idSiteType']->add_rule('required');
			$this->form['BatimentNbr']->add_rule('required');
			$this->form['DateVisite']->add_rule('required');
			
			if ($this->byPass_statut1) $this->form['DatePrevue']->hide();
			
			if (is_null($this->data->DateSaisie)) $this->setdata('DateSaisie',date('Y-m-d'));
		}
		else 
		{
			$this->form['idSiteType']->hide();
			$this->form['DateVisite']->hide();
			$this->form['BatimentNbr']->hide();
			$this->statut = 0;
		}
	}
	
	//RETURN ARRAY WITH INFO
	function info()
	{
		$info = null;
		$info['Nom'] = $this->form['Nom']->get();
		$info['Type'] = $this->form['idSiteType']->get();
		$info['Ministère'] = $this->form['idMinistere']->get();
		$info['Adresse'] = $this->form['Adresse1']->get();
		$info['Adresse'] .= ($this->form['Adresse2']->get() != '')?' - '.$this->form['Adresse2']->get():'';
		$info['Ville'] = $this->form['idVille']->get();
		$info['Bâtiments'] = $this->form['BatimentNbr']->get();
		$info['Date de Visite'] = $this->form['DateVisite']->get();
		$info['Equipe'] = $this->form['idEquipe']->get();
		$info['Photo'] = $this->form['Photo']->get('*NULL','print');
		$info['Plan'] = $this->form['Plan']->get('*NULL','print');
		
		$info['Responsable'] = $this->form['ContactRespNom']->get();
		$info['Responsable'].= ($this->form['ContactRespFonc']->get() != '')?' ('.$this->form['ContactRespFonc']->get().')':'';
		$info['Responsable'].= ($this->form['ContactRespTel']->get() != '')?' - '.$this->form['ContactRespTel']->get():'';
		
		if ($this->form['ContactTechNom']->get() != ''):
			$info['Contact 1'] = $this->form['ContactTechNom']->get();
			$info['Contact 1'].= ($this->form['ContactTechFonc']->get() != '')?' ('.$this->form['ContactTechFonc']->get().')':'';
			$info['Contact 1'].= ($this->form['ContactTechTel']->get() != '')?' - '.$this->form['ContactTechTel']->get():'';
		endif;
		
		if ($this->form['ContactSup2Nom']->get() != ''):
			$info['Contact 2'] = $this->form['ContactSup2Nom']->get();
			$info['Contact 2'].= ($this->form['ContactSup2Fonc']->get() != '')?' ('.$this->form['ContactSup2Fonc']->get().')':'';
			$info['Contact 2'].= ($this->form['ContactSup2Tel']->get() != '')?' - '.$this->form['ContactSup2Tel']->get():'';
		endif;
		
		if ($this->form['ContactSup3Nom']->get() != ''):
			$info['Contact 3'] = $this->form['ContactSup3Nom']->get();
			$info['Contact 3'].= ($this->form['ContactSup3Fonc']->get() != '')?' ('.$this->form['ContactSup3Fonc']->get().')':'';
			$info['Contact 3'].= ($this->form['ContactSup3Tel']->get() != '')?' - '.$this->form['ContactSup3Tel']->get():'';
		endif;
		
		if ($this->form['Commentaire']->get() != '') $info['Observations'] = $this->form['Commentaire']->get();
		
		return $info;
	}
	
	//RETURN DIAG VALUES
	function diag()
	{
		$val = null;
				
		//Superficie
		$val['Surface'] = 0;	
			
		//occupants
		$val['Occupants'] = 0;
		$val['Occupants2'] = 0;
		
		//Nombre de pièces
		$val['Npieces'] = 0;
		
		//puissance installée
		$val['Pinstall'] = 0;
		$val['Pdisj'] = 0;		
		$val['Ptransfo'] = 0;	
		$val['Psouscrite'] = 0;		
		$val['Patteinte'] = 0;	
				
		//Puissance installée éclairage
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
		
		//Puissance clim installée
		$val['Pclim'] = 0;
		$val['Nclim'] = 0;
		
		if (isset($this->PLelec))
		foreach($this->PLelec as $id=>$PL)
		{
			$pld[$id] = $PL->diag();
			foreach($val as $k=>$v) if (isset($pld[$id][$k])) $val[$k] += $pld[$id][$k];
		}
		
		if ($val['Pdisj'] == 0) unset($val['Pdisj']);
		if ($val['Ptransfo'] == 0) unset($val['Ptransfo']);
		if ($val['Patteinte'] == 0) unset($val['Patteinte']);
		
		if (isset($this->Bats)):
			foreach($this->Batiments as $id=>$bat) 
			{
				$bd[$id] = $this->Bats[$id]->diag();
				foreach($val as $k=>$v) if (isset($bd[$id][$k])) $val[$k] += $bd[$id][$k];
			}
			
			$val['Batiments'] = $bd;
		endif;
		
		//ratio Superficie totale
		if ($val['Surface'] > 0)
		{
			$val['Peclair/m²'] = round($val['Peclair']*100/$val['Surface'])/100;
			$val['Pclim/m²'] = round($val['Pclim']*100/$val['Surface'])/100;
		}
		
		//ratio Occupants
		if ($val['Occupants'] > 0)
		{
			$val['Peclair/pers'] = round($val['Peclair']*100/$val['Occupants'])/100;
			$val['Pclim/pers'] = round($val['Pclim']*100/$val['Occupants'])/100;
		}
		
		
		return $val;
		
	}
	
	//Permet le lien entre EnergyBat et EnergyWatch via le numéro de PL
	function get_id_from_numPl($noPL = 0,$idBatiment=null){
		if(BT_MT_EAU=='EAU'){
			$table='Site_PL_eau';
		}
		else{
			$table='Site_PL_elec';
			
		}
		$this->db->where('NoPL', $noPL);
		$this->db->limit(1);
		$query = $this->db->get($table);
		if($query->row()->idSite==''){
			echo 'Ce site n\'a pas encore &eacute;t&eacute; recens&eacute; dans EnergyBat.';
			die();
		}
		return $query->row()->idSite; 
	}
}
