<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Consult extends Controller {
 
 	private $byPass1 = true;  //sauter l'étape 1 du recensement
 	private $Date_c; //date courante pour l'affichage des données
 	private $Date_c_real; //date reel (le 28 du mois précedant)
 	private $EwatchDB;
 	
	function __construct() 
	{
		parent::__construct();
		//$this->output->enable_profiler(true);
		$this->load->helper('charts');
		
		//ACCESS TO EWATCH CONSO
		$this->EwatchDB = $this->load->database('ewatch', TRUE);
		
		//Date de travail=date facture la plus récente
		$this->EwatchDB->select_max('Date_index');
		$query = $this->EwatchDB->get('facturebts');
		
		$row = $query->row(); 

		$date_bt=strtotime($row->Date_index);

		if (date("d",$date_bt)<28){
			$date_bt= date("Y",$date_bt).'-'.(date("n",$date_bt)-1).'-28';
		}
		else{
			$date_bt= date("Y",$date_bt).'-'.date("n",$date_bt).'-28';
		}

		$this->EwatchDB->select_max('Date_index');
		$query2 = $this->EwatchDB->get('facturemts');
		$row2 = $query2->row();
		$date_mt=strtotime($row2->Date_index);
		if (date('d',$date_mt)<28){
			$date_mt= date("Y",$date_mt).'-'.(date("n",$date_mt)-1).'-28';
		}
		else{
			$date_mt= date("Y",$date_mt).'-'.date("n",$date_mt).'-28';
		}
		      
		$date_max_facture=max(strtotime($date_bt),strtotime($date_mt));        

		$this->Date_c_real=$date_max_facture;


		//Recupere un eventuel changement de date courante
		if ($this->input->post('Date_c') > 0) $this->Date_c = $this->input->post('Date_c');
		else $this->Date_c = $this->Date_c_real;    	

		//$this->Date_c = strtotime('2010-11-02'); //Test forced Date courante

	}
 	
	function ForceCache()
	{
		$this->db->update('Site',array('cache' => NULL));
		redirect('consult','refresh');
	}
 	
	function index($return=false) 
    	{    	   	
	    	$data['title'] = "Consultation";
	    	
	      	$this->load->model('List_model','lister');  	
	    	$this->load->model('Site_model','Site'); //LOAD SITE MODEL
	    	
	    	$this->db->select('idSite,cache');
	    	$Sites_liste = $this->db->select('idSite')->where('ValideManu',1)->get('Site');    	    	
	  		
  		$c=0;
  		$SiteIni = new Site_model($this->Date_c_real,false);
		foreach($Sites_liste->result_array() as $s) 
		{
			$c++;
			$SitL = clone $SiteIni; 
			$data['complete'][$s['idSite']] = $SitL->lazy_get($s['idSite'],$s['cache']);  //USE CACHED OBJECT IF AVAILABLE						
			unset($Sitin);
			//if ($c>10) break;
		}
	
 
		//STATS
		$data['count']['total'] = count($Sites_liste);
		$data['count']['complete'] = count($data['complete']);
		////
	    	
	    	//SELECTION MINISTERE
	    	if ($this->input->post('idMinistere') !== false) $this->session->set_userdata('idMinistere_consult',$this->input->post('idMinistere'));
	    	$idM = $this->session->userdata('idMinistere_consult');
	    	if ($idM > 0) 
	    		foreach($data['complete'] as $id=>$dat) if ($dat->idMinistere != $idM) unset($data['complete'][$id]);
	    	
	    	$options[0] = '--Tous--';
	    	$options = array_merge($options,$this->lister->get('Ministere'));
	    	$data['MinistereListe'] = form_dropdown('idMinistere',$options,$idM,'onChange="this.form.submit();"'); 
	    	////

	    	//SELECTION TYPE BATIMENT
	    	if ($this->input->post('idSiteType') !== false) $this->session->set_userdata('idSiteType_consult',$this->input->post('idSiteType'));
	    	$idM = $this->session->userdata('idSiteType_consult');
	    	if ($idM > 0) 
	    		foreach($data['complete'] as $id=>$dat) if ($dat->idSiteType != $idM) unset($data['complete'][$id]);
	    	
	    	$options = array();	
	    	$options[0] = '--Tous--';
	    	$options = array_merge($options,$this->lister->get('SiteType'));
	    	$data['SiteTypeListe'] = form_dropdown('idSiteType',$options,$idM,'onChange="this.form.submit();"'); 
	    	////
	    	
	    	//TRI
	    	if ($this->input->post('order') !== false) $this->session->set_userdata('order_consult',$this->input->post('order'));
	    	$data['Order'] = $this->session->userdata('order_consult');
	    	if(!$data['Order']) $data['Order'] = 'Site';
	    	
	    	if ($data['Order'] == 'id') ksort($data['complete']);
	    	else
	    	{
				switch($data['Order'])
				{
					case 'Site':
						foreach($data['complete'] as $id=>$dat)
							$dataO[$id] = strtolower($dat->Nom);
						
						asort ($dataO);
					break;
					case 'Ministere':
						foreach($data['complete'] as $id=>$dat)
							$dataO[$id] = strtolower($dat->Ministere);
						
						asort ($dataO);
					break;
					case 'KWh/an':
						foreach($data['complete'] as $id=>$dat) 
							$dataO[$id] = 365*($dat->{'Ratio'}['elec']['12m']['KWh/J']);
						
						arsort ($dataO);
					break;
					case 'KWh/m²/J':
						foreach($data['complete'] as $id=>$dat) 
							$dataO[$id] = $dat->{'Ratio'}['elec']['12m']['KWh/m²'];
						
						arsort ($dataO);
					break;	
					
					case 'KWh/hab/J':
						foreach($data['complete'] as $id=>$dat) 
							$dataO[$id] = $dat->{'Ratio'}['elec']['12m']['KWh/hab'];
						
						arsort ($dataO);
					break;
					
					case 'L/hab/J':	
						foreach($data['complete'] as $id=>$dat) 
							$dataO[$id] = $dat->{'Ratio'}['eau']['30j']['L/hab/J'];	
					
						arsort ($dataO);
					break;
				}
			
				//COMMON PROCESS
				$dT = $data['complete'];
				$data['complete'] = array();
				foreach ($dataO as $id=>$v) $data['complete'][$id] = $dT[$id];
			}
			/////
	    	
	    	//GRAPHIQUES:
	    	////1MOIS
		//KWh / m²
	    	$Graph = new Charts('Electricité : KWh par m2 sur 1 mois, réparti en fonction de la superficie du Site',1100,350,'Surface Totale (m²)',false,'KWh/m²/mois');
			$Graph->serie('KWh/m2',3,'orange');
		
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Surface,$site->Ratio['elec']['30j']['KWh/m²'],$site->Nom,$id);					
			//$data['GraphElec1M'] = $Graph->Graph('scatter');
			$data['GraphElec1M'] = $Graph->ScatterJS();
			unset ($Graph);
	
		//KWh / hab
	    	$Graph = new Charts('Electricité : KWh par Occupant sur 1 mois, réparti en fonction du nombre d Occupants',1100,350,'Occupants',false,'KWh/hab/mois');
			$Graph->serie('KWh/hab',20,'red');
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Occupants,$site->Ratio['elec']['30j']['KWh/hab'],$site->Nom,$id);					
			//$data['GraphElec2M'] =  $Graph->Graph('scatter');
			$data['GraphElec2M'] = $Graph->ScatterJS();
			unset ($Graph);
	
		//L / hab / J
	    	$Graph = new Charts('Eau : Litres par Jour et par Occupant, réparti en fonction du nombre d Occupants (sur 1 mois)',1100,350,'Occupants',false,'L/hab/J');
			$Graph->serie('L/hab/J',20,'blue');
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Occupants,$site->Ratio['eau']['30j']['L/hab/J'],$site->Nom,$id);					
			//$data['GraphEauM'] =  $Graph->Graph('scatter');
			$data['GraphEauM'] = $Graph->ScatterJS();
			unset ($Graph);
	    	
	    	////1AN
		//KWh / m²
	    	$Graph = new Charts('Electricité : KWh par An et par m2, réparti en fonction de la superficie du Site',1100,350,'Surface Totale (m2)',false,'KWh/m²/an');
			$Graph->serie('KWh/m2/an',20,'orange');
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Surface,$site->Ratio['elec']['12m']['KWh/m²']*365/30,$site->Nom,$id);					
			//$data['GraphElec1A'] = $Graph->Graph('scatter');
			$data['GraphElec1A'] = $Graph->ScatterJS();
			unset ($Graph);
	
		//KWh / hab
	    	$Graph = new Charts('Electricité : KWh par An et par Occupant, réparti en fonction du nombre d Occupants',1100,350,'Occupants',false,'KWh/hab/an');
			$Graph->serie('KWh/hab/an',20,'red');
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Occupants,$site->Ratio['elec']['12m']['KWh/hab']*365/30,$site->Nom,$id);					
			//$data['GraphElec2A'] =  $Graph->Graph('scatter');
			$data['GraphElec2A'] = $Graph->ScatterJS();
			unset ($Graph);
	
		//L / hab / J
	    	$Graph = new Charts('Eau : Litres par Jour et par Occupant, réparti en fonction du nombre d Occupants (moyenne sur 12 mois)',1100,350,'Occupants',false,'L/hab/J');
			$Graph->serie('L/hab/J',20,'blue');
			foreach($data['complete'] as $id=>$site) $Graph->point($site->Occupants,$site->Ratio['eau']['12m']['L/hab/J'],$site->Nom,$id);					
			//$data['GraphEauA'] =  $Graph->Graph('scatter');
			$data['GraphEauA'] = $Graph->ScatterJS();
			unset ($Graph);
	  	
	    	if ($return) return $data;
			else $this->viewlib->view('consult/browse',$data);
	}

	function printList()
	{
	    	//load index & get data
	    	$data = $this->index(true);
	    	
	    	//create Table to print (one site per line)
	    	$infoG = null;
	    	$info = null;
	    	$infoLEG = array('id','Site','KWh/hab/An','KWh/m²/An','L/hab/J','P. Clim','P. clim /m²');
	    	$info[0] = $infoLEG;
	    	foreach ($data['complete'] as $id=>$site):
	    		$info [] = array(
	    							$id,
	    							$site->Nom,
	    							($site->Ratio['elec']['12m']['KWh/hab'] > 0)?(round($site->Ratio['elec']['12m']['KWh/hab']*365*100/30)/100).' KWh':'',
	    							($site->Ratio['elec']['12m']['KWh/m²'] > 0)?(round($site->Ratio['elec']['12m']['KWh/m²']*365*100/30)/100).' KWh':'',
	    							($site->Ratio['eau']['30j']['L/hab/J'] > 0)?($site->Ratio['eau']['30j']['L/hab/J']).' L':'',
	    							($site->Ratio['clim']['Puissance'] > 0)?(round(($site->Ratio['clim']['Puissance'])/10)/100).' KW':'',
	    							($site->Ratio['clim']['Puissance/m²'] > 0)?($site->Ratio['clim']['Puissance/m²']).' W/m²':''
	    							);
	    	
				if (count($info) > 50)
				{
					$infoG[] = $info;
					$info = null;
	    			$info[0] = $infoLEG;
				}     	
	    	endforeach;
	    	if (count($info) > 1) $infoG[] = $info;
	    	
	    		
	    	$align = array (
	    							'left',
	    							'left',
	    							'left',
	    							'right',
	    							'right',
	    							'right',
	    							'right',
	    							'right'
	    						);
	    	
	    	//build & display 
	    	$this->load->library('pdf');
			$this->pdf->SetFileNamePDF('listing_sites');
		
			$this->pdf->SetSubject('Listing des Sites');
			$this->pdf->SetFont('helvetica');
		
			foreach ($infoG as $info) 
			{
				$this->pdf->AddPage();
			
				//Title
				$this->pdf->makeTitle('Listing des Sites');
			
				//print info
				$title = '';
				$idM = $this->session->userdata('idMinistere_consult');
	    		//$title .= 'Ministère : '.$idM.' - ';
	    		
	    		$idM = $this->session->userdata('idSiteType_consult');
	    		//$title .= 'Type de Site : '.$idM.' - ';  
	    		
	    		$idM = $this->session->userdata('order_consult');
	    		//$title .= 'Tri : '.$idM.' ';  
			
	    		$this->pdf->makeSubTitle($title);
				$this->pdf->makeTable($info,true,false,69,true,$align);		
			}
		
			//Close and output PDF document
			$data['ToDo'] = $this->pdf->ClosePDF();
	    	
			//Call view!
	    	$this->viewlib->view('consult/browse',$data);
	}
    
    function site($idSite = 0,$idBatiment=null)
    {
    	if ($idSite > 0)
    	{
    		$data['title'] = "Consultation";
    		$data['mode'] = $this->uri->segment(1);
    		$data['byPass1'] = $this->byPass1;
    		
		$data['idSite'] = $idSite;    	
			
		//$this->load->model('Site_model','Site'); //LOAD SITE MODEL
		$this->load->model('Site_model','Site');
		$this->Site = new Site_model($this->Date_c_real);
		$this->Site->get($idSite,false,false,$this->byPass1,$this->Date_c); //LOAD SITE
	
		if ($this->Site->process()) $idBatiment = null; //EXECUTE SITE ACTIONS (save/edit/delete..)
		$data['Site'] = $this->Site->formfields();

		if ($this->Site->statut >= 3) 
		{	
			$data['Statut'] = '<div style="color:green">recensement terminé</div>';
			
			$data['MoisCourant'] = date('m/Y',$this->Date_c);
			$data['MoisPrecedent'] = date('m/Y',strtotime('-1 month',$this->Date_c));
			$data['MoisAnPrecedent'] = date('m/Y',strtotime('-1 year',$this->Date_c));
			
			$options[$this->Date_c_real] = date('m/Y',$this->Date_c_real);
			
			for($i=1;$i<13;$i++)
			{
				$dat = strtotime('-'.$i.' month',$this->Date_c_real);
				$options[$dat] = date('m/Y',$dat);
			}
			
			$data['SelectMois'] = form_dropdown('Date_c',$options,$this->Date_c,'onChange="this.form.submit();"');
			
			if (count($this->Site->onWork) == 0) 
			{
				$data['Suivi'] = true;
				$data['Diagnostic'] = true;
			}
			else 
			{
				$data['Suivi'] = false;
				$data['Diagnostic'] = false;
			}
			
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
			
			$data['Site']['BATcount'] = count($data['BatimentsList']);
			$data['Site']['PLcount'] = count($data['PLeauList'])+count($data['PLgasoilList'])+count($data['PLelecList']);
			$data['Site']['Surface'] = $this->Site->Surface;
			$data['Site']['Occupants'] = $this->Site->Occupants;
			$data['Site']['Occupants2'] = $this->Site->Occupants2;
			
			//DIAG VALUES
			$diagS = $this->Site->diag();
			$diagB =  $diagS['Batiments'];
			unset ($diagS['Batiments']);
						
			//index list to display, with rule
				$toDisp = array(
					'Npieces' 		=> array('Pieces'),
					' ' 			=> null,
					'Ptransfo'		=> array('P. transfo',' kW'),
					'Pdisj'			=> array('P. disjoncteur',' kW',0.001),
					'Psouscrite'	=> array('P. souscrite',' kW'),
					'Patteinte'		=> array('P. atteinte',' kW'),
					'Pinstall'		=> array('P. installée',' kW',0.001),
					'  ' 			=> null,
					'NeclairIncandesc'	=> array('N. incand',' points'),
					'NeclairBasseConso'	=> array('N. BC',' points'),
					'NeclairLustre'	=> array('N. lustre',' points'),
					'NeclairNeon'	=> array('N. neon',' points'),
					'Neclair'		=> array('N. eclairage',' points'),
					'Peclair'		=> array('P. eclairage',' kW',0.001),
					'   ' 			=> null,
					'Nclim'			=> array('N. clim',' points'),
					'Pclim'			=> array('P. clim',' kW',0.001),
					'    ' 			=> null,
					'Surface' 		=> array('Surface',' m²'),
					'Peclair/m²'	=> array('P. eclair /m²',' W/m²'),
					'Pclim/m²'		=> array('P. clim /m²',' W/m²'),
					'     ' 		=> null,
					);
			if ($data['Site']['idSiteType']=='Ecole'){
				$toMerge = array(
					'Occupants' 	=> array('Administratifs',' pers'),
					'Occupants2' 	=> array('Eleves',' pers'),
					'Peclair/pers'	=> array('P. eclair /pers',' W/p'),
					'Pclim/pers'	=> array('P. clim /pers',' W/p'),					
					);
			}
			else{
				$toMerge = array(
					'Occupants' 	=> array('Occupants',' pers'),
					'Peclair/pers'	=> array('P. eclair /pers',' W/p'),
					'Pclim/pers'	=> array('P. clim /pers',' W/p'),					
					);
			}
			$toDisp= array_merge($toDisp,$toMerge);
			
					
			function buildDiag($toDisp,$diag,$hideEmpty=false)
			{
				$l=' ';
				$br=false;
				foreach ($toDisp as $k=>$method) 
				{
					if ((trim($k) == '')&&($br)) {$l .= ' ';	$return[$l] = ''; $br=false;} //break line
					else if (isset($diag[$k]))
					{
						if (isset($method[0])) $kn = $method[0];
						else $kn = $k;
						
						if (isset($method[2])) $v = round($diag[$k]*100*$method[2])/100;
						else $v = $diag[$k];
						
						if (($v <> 0)&&(trim($v) != ''))
						{
							$return[$kn] = $v;
							if (isset($method[1])) $return[$kn] .= $method[1];
							$br=true;
						}
						else if (!$hideEmpty) $return[$kn] = '';
					}
				}	
				return $return;
			}
			
			//BUILD SITE DIAG
			$data['Site']['Values'] = buildDiag($toDisp,$diagS,true);
			
			//BUILD DIAG FOR EACH BAT
			foreach($diagB as $idBat=>$dB)
			{
				$td = null;
				
				$diagN = $dB['Niveaux'];
				unset($dB['Niveaux']);
				
				$total = buildDiag($toDisp,$dB);
				$td[' '] = array('Total',' ');
				
				foreach($total as $k=>$val) 
				{
					$td[$k][] = $val;
					$td[$k][] = ' ';
				}
				
				foreach ($diagN as $niv=>$dN) 
				{
					$nd = buildDiag($toDisp,$dN);
					
					$td[' '][] = 'Niveau '.$niv;
					foreach($td as $k=>$lin) if (isset($nd[$k])) $td[$k][] = $nd[$k];
				}
				
				$nam = $this->Site->Batiments[$idBat];
				$data['Batiments']['Values'][$nam] = $td;
			}
			
			//RATIOS
			$data['Site']['Ratio'] = $this->Site->Ratio;
			
			//GRAPHS & DATAS
			//KWh / mois
			$Graph = new Charts('Electricité : MWh par mois, sur 13 mois',550,350,null,true);
					
			$Graph->serie('MWh 3',null,'red');
			foreach ($this->Site->Ratio['elec']['mois'] as $mois=>$vals)	
			{	
				$Graph->point($mois,$vals['MWhMTP']+$vals['MWhMTHP']+$vals['MWhBT'],'MT P');
				$data['Site']['Datas']['MT P (MWh)'][$mois] = $vals['MWhMTP'];
			}
			
			$Graph->serie('MWh 2',null,'yellow');
			foreach ($this->Site->Ratio['elec']['mois'] as $mois=>$vals)	
			{
				$Graph->point($mois,$vals['MWhMTHP']+$vals['MWhBT'],'MT HP');
				$data['Site']['Datas']['MT HP (MWh)'][$mois] = $vals['MWhMTHP'];		
			}
			
			$Graph->serie('MWh');
			foreach ($this->Site->Ratio['elec']['mois'] as $mois=>$vals)	
			{
				$Graph->point($mois,$vals['MWhBT'],'BT');	
				$data['Site']['Datas']['BT (MWh)'][$mois] = $vals['MWhBT'];		
			}
			
			$data['Site']['Graph']['ConsoElec1'] = $Graph->Graph('bar');
			if ($idBatiment == 'printInfo') $data['Site']['GraphImg']['ConsoElec1'] = $Graph->GraphReal('bar','ConsoElec1','Print');
			unset ($Graph);
			
			//m3 / mois
			$Graph = new Charts('Eau : m3 par mois, sur 13 mois',550,350);	
			
			$Graph->serie('m3',null,'blue');
			foreach ($this->Site->Ratio['eau']['mois'] as $mois=>$vals)	
			{
				$Graph->point($mois,$vals['m3'],'volume');		
				$data['Site']['Datas']['Eau (m3)'][$mois] = $vals['m3'];
			}
			
			$data['Site']['Graph']['ConsoEau1'] = $Graph->Graph('bar');
			if ($idBatiment == 'printInfo') $data['Site']['GraphImg']['ConsoEau1'] = $Graph->GraphReal('bar','ConsoEau1','Print');
			unset ($Graph);
			////
			
			$data['idBatiment'] = $idBatiment;
			$data['Batiment']=null;
					
			//LOAD BATIMENT
			if (isset($this->Site->Bats[$idBatiment]))
			{				
				$bat = $this->Site->Bats[$idBatiment];
				$bat->process($idSite);
				
				$data['Batiment'] = array_merge($bat->Bat_Occupation->formfields(),$bat->Bat_Technique->formfields(),$bat->Bat_Equip_Air->formfields());
				$data['Batiment'] = array_merge($data['Batiment'],$bat->formfields());
				
				for($i=1;$i<=$bat->data->NiveauNbr;$i++) 
				{
					$Niv = $bat->Bat_Niveaux[$i]->formfields();
					foreach($Niv as $key=>$input) $data['Batiment']['Niveaux'][str_replace('_'.$i,'',$key)][$i] = $input;
				}
				
				$data['Batiment']['Statut'] = $bat->statut;
			}
			
			else if ($idBatiment == 'printDiag')
			{
				$this->load->library('pdf');
				$this->pdf->SetFileNamePDF('site_'.$idSite);
				
				$this->pdf->SetSubject('Diagnostic Site '.$idSite.' : '.$data['Site']['Nom']);
				$this->pdf->AddPage();
				$this->pdf->SetFont('helvetica');
				
				//Title
				$this->pdf->makeTitle('DIAGNOSTIC SITE '.$idSite.' - '.$data['Site']['Nom']);
				
				//site diag
				$this->pdf->makeSubTitle('Site Complet');
				$this->pdf->makeList($data['Site']['Values'],'list');
				
				//bats diag
				$kc = 0;		
				foreach($data['Batiments']['Values'] as $nam=>$diag) 
				{
					if (($kc == 0) or ($kc == 2))
					{
						$this->pdf->AddPagePDF();
						$this->pdf->makeTitle('DIAGNOSTIC SITE '.$idSite.' - '.$data['Site']['Nom']);
						$kc = 0;
					}
					$this->pdf->makeSubTitle('BATIMENT - '.$nam);
					$this->pdf->makeTable($diag);
					$kc++;
				}
				
				//Close and output PDF document
				$data['ToDo'] = $this->pdf->ClosePDF();
			}
			
			//PRINT SITE INFO
			else if ($idBatiment == 'printInfo')
			{
				$this->load->library('pdf');
				$this->pdf->SetFileNamePDF('site_'.$idSite);
				
				$this->pdf->SetSubject('Site '.$idSite.' : '.$data['Site']['Nom']);
				$this->pdf->AddPage();
				$this->pdf->SetFont('helvetica');
				
				//Title
				$this->pdf->makeTitle('SITE '.$idSite.' - '.$data['Site']['Nom']);
				
				//site info
				$this->pdf->makeSubTitle('INFORMATIONS SITE');
				$this->pdf->makeList($this->Site->info(),'list');
				
				//PLs info	
				$this->pdf->makeSubTitle('LIVRAISON ELECTRICITE');	
				foreach($this->Site->PLelec as $id=>$pl) 	$this->pdf->makeList($pl->info(),'inline');
				
				$this->pdf->makeSubTitle('LIVRAISON EAU');
				foreach($this->Site->PLeau as $id=>$pl) 	$this->pdf->makeList($pl->info(),'inline');
				
				$this->pdf->makeSubTitle('LIVRAISON GASOIL');
				foreach($this->Site->PLgasoil as $id=>$pl) 	$this->pdf->makeList($pl->info(),'inline');
		
				//Page 2
				$this->pdf->AddPagePDF();
				$this->pdf->makeTitle('SITE '.$idSite.' - '.$data['Site']['Nom']);
				
				//Suivi ELEC		
				$this->pdf->makeSubTitle('SUIVI ELECTRICITE');
				//echo 'okidsite'.$this->Site->idSiteType; die;
				if ($data['Site']['idSiteType']=='Ecole'){
					$tbl1 = array(
							'Superficie'	=> $data['Site']['Surface'].' m²',
							'Administratifs' 	=> $data['Site']['Occupants'].' pers',
							'Elèves' 	=> $data['Site']['Occupants2'].' pers',
							'Facture 1 an' 	=> ($data['Site']['Ratio']['elec']['12m']['MFm']*12).' MCFA',
							'Conso 1 an' 	=> (round($data['Site']['Ratio']['elec']['12m']['KWh/J']*365/10)/100).' MWh'
							);
				}
				else{
					$tbl1 = array(
							'Superficie'	=> $data['Site']['Surface'].' m²',
							'Occupants' 	=> $data['Site']['Occupants'].' pers',
							'Facture 1 an' 	=> ($data['Site']['Ratio']['elec']['12m']['MFm']*12).' MCFA',
							'Conso 1 an' 	=> (round($data['Site']['Ratio']['elec']['12m']['KWh/J']*365/10)/100).' MWh'
							);
				}
				
				$tbl2 = array(
						' '			=> array($data['MoisCourant'],$data['MoisPrecedent'],$data['MoisAnPrecedent'],'Moy. 1 an'),
						'Facture (kCFA)'	=> array(($data['Site']['Ratio']['elec']['30j']['MFm']*1000),($data['Site']['Ratio']['elec']['30+30j']['MFm']*1000),($data['Site']['Ratio']['elec']['365+30j']['MFm']*1000),($data['Site']['Ratio']['elec']['12m']['MFm']*1000)),
						'Cout KWh (CFA)'	=> array($data['Site']['Ratio']['elec']['30j']['CK'],$data['Site']['Ratio']['elec']['30+30j']['CK'],$data['Site']['Ratio']['elec']['365+30j']['CK'],$data['Site']['Ratio']['elec']['12m']['CK']),
						'PA/PS (KW)'		=> array($data['Site']['Ratio']['elec']['30j']['PAt'].'/'.$data['Site']['Ratio']['elec']['30j']['PSt'],$data['Site']['Ratio']['elec']['30+30j']['PAt'].'/'.$data['Site']['Ratio']['elec']['30+30j']['PSt'],$data['Site']['Ratio']['elec']['365+30j']['PAt'].'/'.$data['Site']['Ratio']['elec']['365+30j']['PSt'],$data['Site']['Ratio']['elec']['12m']['PAt'].'/'.$data['Site']['Ratio']['elec']['12m']['PSt']),
						'KWh'			=> array(($data['Site']['Ratio']['elec']['30j']['KWh/J']*30),($data['Site']['Ratio']['elec']['30+30j']['KWh/J']*30),($data['Site']['Ratio']['elec']['365+30j']['KWh/J']*30),($data['Site']['Ratio']['elec']['12m']['KWh/J']*30)),
						'KWh /jour'		=> array($data['Site']['Ratio']['elec']['30j']['KWh/J'],$data['Site']['Ratio']['elec']['30+30j']['KWh/J'],$data['Site']['Ratio']['elec']['365+30j']['KWh/J'],$data['Site']['Ratio']['elec']['12m']['KWh/J']),
						'KWh /m²'		=> array($data['Site']['Ratio']['elec']['30j']['KWh/m²'],$data['Site']['Ratio']['elec']['30+30j']['KWh/m²'],$data['Site']['Ratio']['elec']['365+30j']['KWh/m²'],$data['Site']['Ratio']['elec']['12m']['KWh/m²']),
						'KWh /hab'		=> array($data['Site']['Ratio']['elec']['30j']['KWh/hab'],$data['Site']['Ratio']['elec']['30+30j']['KWh/hab'],$data['Site']['Ratio']['elec']['365+30j']['KWh/hab'],$data['Site']['Ratio']['elec']['12m']['KWh/hab'])						
						);
				
				$this->pdf->writeHTML('<table><tr><td width="80mm">'.$data['Site']['GraphImg']['ConsoElec1'].'</td><td width="5mm"></td><td width="90mm">'.$this->pdf->makeList($tbl1,'inline').'<br />'.$this->pdf->makeTable($tbl2,false).'</td></tr></table>');
				
				
				
				//Suivi EAU
				$this->pdf->makeSubTitle('SUIVI EAU');
				if ($data['Site']['idSiteType']=='Ecole'){
					$tbl1 = array(
							'Superficie'	=> $data['Site']['Surface'].' m²',
							'Administratifs' 	=> $data['Site']['Occupants'].' pers',
							'Elèves' 	=> $data['Site']['Occupants2'].' pers',
							'Facture 1 an' 	=> ($data['Site']['Ratio']['eau']['12m']['MFm']*12).' MCFA',
							'Conso 1 an' 	=> ($data['Site']['Ratio']['eau']['12m']['m3']*12).' m3'
							);
				}
				else{
					$tbl1 = array(
							'Superficie'	=> $data['Site']['Surface'].' m²',
							'Occupants' 	=> $data['Site']['Occupants'].' pers',
							'Facture 1 an' 	=> ($data['Site']['Ratio']['eau']['12m']['MFm']*12).' MCFA',
							'Conso 1 an' 	=> ($data['Site']['Ratio']['eau']['12m']['m3']*12).' m3'
							);
				}
				
				$tbl2 = array(
						' '			=> array($data['MoisCourant'],$data['MoisPrecedent'],$data['MoisAnPrecedent'],'Moy. 1 an'),
						'Facture (kCFA)'	=> array(($data['Site']['Ratio']['eau']['30j']['MFm']*1000),($data['Site']['Ratio']['eau']['30+30j']['MFm']*1000),($data['Site']['Ratio']['eau']['365+30j']['MFm']*1000),($data['Site']['Ratio']['eau']['12m']['MFm']*1000)),
						'Cout m3 (CFA)'		=> array($data['Site']['Ratio']['eau']['30j']['CM3'],$data['Site']['Ratio']['eau']['30+30j']['CM3'],$data['Site']['Ratio']['eau']['365+30j']['CM3'],$data['Site']['Ratio']['eau']['12m']['CM3']),
						'm3'			=> array($data['Site']['Ratio']['eau']['30j']['m3'],$data['Site']['Ratio']['eau']['30+30j']['m3'],$data['Site']['Ratio']['eau']['365+30j']['m3'],$data['Site']['Ratio']['eau']['12m']['m3']),
						'L/hab/J'		=> array($data['Site']['Ratio']['eau']['30j']['L/hab/J'],$data['Site']['Ratio']['eau']['30+30j']['L/hab/J'],$data['Site']['Ratio']['eau']['365+30j']['L/hab/J'],$data['Site']['Ratio']['eau']['12m']['L/hab/J'])						
						);
				
				$this->pdf->writeHTML('<table><tr><td width="80mm">'.$data['Site']['GraphImg']['ConsoEau1'].'</td><td width="5mm"></td><td width="90mm">'.$this->pdf->makeList($tbl1,'inline').'<br />'.$this->pdf->makeTable($tbl2,false).'</td></tr></table>');
				
				//BATIMENTS
				if (isset($this->Site->Batiments)):
					$this->load->model('Batiment_model','Batiment'); //LOAD BAT MODEL
					
					foreach($this->Site->Batiments as $idBat=>$Nom)
					{
						
						$bat = $this->Site->Bats[$idBat]; //$this->Batiment->get($idBat); //LOAD BAT
						
						if ($bat->statut >= 3):
						
							//Page sup
							$this->pdf->AddPagePDF();
							$this->pdf->makeTitle('SITE '.$idSite.' - '.$data['Site']['Nom']);
							$this->pdf->makeSubTitle('BATIMENT - '.$Nom);
							
							//Load
							$bat->formfields();
							$bat->Bat_Occupation->formfields();
							$bat->Bat_Technique->formfields();
							$bat->Bat_Equip_Air->formfields();
							
							//info Bat
							$this->pdf->writeHTML('<table><tr>
										<td width="70mm">'.$this->pdf->makeList($bat->info(),'list',false).'</td>
										<td width="50mm">'.$this->pdf->makeList($bat->Bat_Occupation->info(),'list',false).'</td>
										<td width="70mm">'.$this->pdf->makeList($bat->Bat_Technique->info(),'list',false).'</td>
										</tr></table>');
							
							//Air/Froid
							$this->pdf->makeSubTitle('BATIMENT - '.$Nom.' - AIR ET FROID');
							$this->pdf->makeTable($bat->Bat_Equip_Air->info(),true,true);
							
							//Equipements Niveaux
							//Page sup
							$this->pdf->AddPagePDF();
							$this->pdf->makeTitle('SITE '.$idSite.' - '.$data['Site']['Nom']);
							$this->pdf->makeSubTitle('BATIMENT - '.$Nom.' - EQUIPEMENTS PAR NIVEAU');
							
							$info = array();
							for($i=1;$i<=$bat->data->NiveauNbr;$i++) 
							{
								$bat->Bat_Niveaux[$i]->formfields();
								
								$info[' '][] = 'Niveau '.$i;
								foreach($bat->Bat_Niveaux[$i]->info() as $idI=>$val) $info[$idI][] = $val;
							}
														
							$this->pdf->makeTable($info,true,true,26);					
							
						endif;
					}	
				endif;	
				
				//Close and output PDF document
				$data['ToDo'] = $this->pdf->ClosePDF();

				
			}
			
			//FACTURES
				$nombre_plbt=0;
				$nombre_plmt=0;
				foreach ($this->Site->PLelec as $PL){
					//BT
					
					//$this->EwatchDB->where('Point_de_livraison', $PL->data->NoPL);
					//$this->EwatchDB->order_by("Date_index", "desc");
					//$query = $this->EwatchDB->get('facturebts');
					
					$query = $this->EwatchDB->query("SELECT * FROM `facturebts`, `pls`, `facturebts_pls` 
								 WHERE pls.Point_de_livraison = '".$PL->data->NoPL."' 
								 AND facturebts_pls.pl_id = pls.id 
								 AND facturebts.id = facturebts_pls.facturebt_id ORDER BY Date_index DESC");
					
					
					if ($query->num_rows() > 0) $nombre_plbt++;
					$compteur=0;
					foreach ($query->result() as $row){
						foreach ($row as $field=>$value){
							$array_data[$field]=$value;
						}
						if($compteur>0){
						//Vérification continuité des factures
							
							$date_index2=strtotime($array_data['Date_index']);
							$nombre_jours=$array_data['Nb_jours'];
							$jours_manquants=round((strtotime($date_debut)-$date_index2)/(3600*24))-1;
							if ($jours_manquants>0){
								$data['Facture']['BT'][$nombre_plbt][$date_index2+1]=array('alerte'=>'Attention période de '.$jours_manquants.' jour(s) manquante.');
							}
							elseif ($jours_manquants<0){
								$jours_manquants=abs($jours_manquants);
								$data['Facture']['BT'][$nombre_plbt][$date_index2+1]=array('alerte'=>'Attention, superposition des factures sur une période de '.$jours_manquants.' jour(s).');
							}
						}
						$compteur++;
						$date_index1=strtotime($array_data['Date_index']);
						$array_data['Date_index']=date( 'd-m-Y', $date_index1);
						$date_debut=date( 'd-m-Y', $date_index1-($array_data['Nb_jours']-1)*3600*24);
						$array_data['Date debut']=$date_debut;
						$data['Facture']['BT'][$nombre_plbt][strtotime($row->Date_index)]=$array_data;
					}
					
					//MT
					//$this->EwatchDB->where('Point_de_livraison', $PL->data->NoPL);
					//$this->EwatchDB->order_by("Date_index", "desc");
					//$query2= $this->EwatchDB->get('facturemts');
					
					$query2 = $this->EwatchDB->query("SELECT * FROM `facturemts`, `pls`, `facturemts_pls` 
								 WHERE pls.Point_de_livraison = '".$PL->data->NoPL."' 
								 AND facturemts_pls.pl_id = pls.id 
								 AND facturemts.id = facturemts_pls.facturemt_id ORDER BY Date_index DESC");
					
					if ($query2->num_rows() > 0) $nombre_plmt++;
					$compteur=0;
					foreach ($query2->result() as $row){
						foreach ($row as $field=>$value){
							$array_data2[$field]=$value;
						}
						if($compteur>0){
						//Vérification continuité des factures, intercale une ligne d'erreur
							$date_index2=strtotime($array_data2['Date_index']);
							$nombre_jours=$array_data2['Nb_jours'];
							$jours_manquants=round((strtotime($date_debut)-$date_index2)/(3600*24))-1;
							if ($jours_manquants>0){
								$data['Facture']['MT'][$nombre_plmt][$date_index2+1]=array('alerte'=>'Attention période de '.$jours_manquants.' jour(s) manquante.');
							}
							elseif ($jours_manquants<0){
								$jours_manquants=abs($jours_manquants);
								$data['Facture']['MT'][$nombre_plmt][$date_index2+1]=array('alerte'=>'Attention, superposition des factures sur une période de '.$jours_manquants.' jour(s).');
							}
						}
						$compteur++;
						$date_index1=strtotime($array_data2['Date_index']);
						$array_data2['Date_index']=date( 'd-m-Y', $date_index1);
						$date_debut=date( 'd-m-Y', $date_index1-($array_data2['Nb_jours']-1)*3600*24);
						$array_data2['Date debut']=$date_debut;
						$data['Facture']['MT'][$nombre_plmt][strtotime($row->Date_index)]=$array_data2;
					}					
				}
				
				$nombre_pl=0;
				foreach ($this->Site->PLeau as $PL){
					//EAU
					$nombre_pl++;
					
					//$this->EwatchDB->where('Point_de_livraison', $PL->data->NoPL);
					//$this->EwatchDB->order_by("Date_index", "desc");
					//$query = $this->EwatchDB->get('facturebts');
					
					$query = $this->EwatchDB->query("SELECT * FROM `facturebts`, `pls`, `facturebts_pls` 
								 WHERE pls.Point_de_livraison = '".$PL->data->NoPL."' 
								 AND facturebts_pls.pl_id = pls.id 
								 AND facturebts.id = facturebts_pls.facturebt_id ORDER BY Date_index DESC");
					
					$compteur=0;
					
					foreach ($query->result() as $row){
						foreach ($row as $field=>$value){
							$array_data[$field]=$value;
						}
						if($compteur>0){
						//Vérification continuité des factures
							
							$date_index2=strtotime($array_data['Date_index']);
							$nombre_jours=$array_data['Nb_jours'];
							$jours_manquants=round((strtotime($date_debut)-$date_index2)/(3600*24))-1;
							if ($jours_manquants>0){
								$data['Facture']['EAU'][$nombre_pl][$date_index2+1]=array('alerte'=>'Attention période de '.$jours_manquants.' jour(s) manquante.');
							}
							elseif ($jours_manquants<0){
								$jours_manquants=abs($jours_manquants);
								$data['Facture']['EAU'][$nombre_pl][$date_index2+1]=array('alerte'=>'Attention, superposition des factures sur une période de '.$jours_manquants.' jour(s).');
							}
						}
						$compteur++;
						$date_index1=strtotime($array_data['Date_index']);
						$array_data['Date_index']=date( 'd-m-Y', $date_index1);
						$date_debut=date( 'd-m-Y', $date_index1-($array_data['Nb_jours']-1)*3600*24);
						$array_data['Date debut']=$date_debut;
						$data['Facture']['EAU'][$nombre_pl][strtotime($row->Date_index)]=$array_data;
					}
				}
				
				
			//Alertes
				
				if (isset($this->Site->Alerte['RougeActive']))
				{
					$data['Alerte']['RougeActive']=$this->Site->Alerte['RougeActive'];
				}
			
				$tableau_flux=array('elec','eau');
				foreach ($tableau_flux as $flux)
				{
					if (isset($this->Site->Alerte[$flux]))
					{
						foreach ($this->Site->Alerte[$flux] as $idAlerteType=>$Alert)
						{
							foreach($Alert as $idAlerte=>$al)
							{
								$var=$al->formfields();
								$data['Alerte'][$flux][$var['idAlerteType']][$idAlerte]=$var;
							}
						}
					}
				}
			$this->viewlib->view('site_display',$data);
		}
    	}
    	else redirect('/consult', 'refresh');
    }
	function getSitefromNoPL($noPL = 0,$BT_MT_EAU){
		$this->load->model('Site_model','Site');
		$this->Site = new Site_model($this->Date_c_real);
		$idPL=$this->Site->get_id_from_numPl($noPL,$BT_MT_EAU);
		$this->site($idPL);
	}
	
    
}
