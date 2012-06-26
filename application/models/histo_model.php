<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//Classe histo commune
class histo_model extends Gwikid_model
{	
	public $calendar;
	protected $debut;
	protected $fin;
	protected $emptydays;
	protected $raws;
	protected $histoSize;
	private $Date_c; //Date courante
	protected $EwatchDB;
	
	function __construct($NoPL,$sufix,$histoSize,$Date_c)  //$type elec_BT, elec_MT , eau
	{
		parent::__construct();

		//Gerer la date courante
		$this->Date_c = $Date_c;
		
		//alignement au jour de la date courante
		$this->Date_c = strtotime(date('Y-m-d',$this->Date_c));
		
		//echo date('Y-m-d',$this->Date_c).'<br>';
		
		$this->calendar = null;
		$this->histoSize = $histoSize;
		$this->limitFin = strtotime("+2 month",$this->Date_c);
		$this->limitDebut = strtotime('-'.$this->histoSize.' day',$this->limitFin);
		
		//ACCESS TO EWATCH CONSO
		$this->EwatchDB = $this->load->database('ewatch', TRUE);
		
		if ($NoPL != '')
		{
			if ($sufix == 'BT') {$suf = 'bts'; $suf2 = 'bt';}
			else if ($sufix == 'MT') {$suf = 'mts'; $suf2 = 'mt';}
			else if ($sufix == 'EAU') {$suf = 'eaux'; $suf2 = 'eau';}
			
			$query = $this->EwatchDB->query("SELECT * FROM `facture".$suf."`, `pls`, `facture".$suf."_pls` 
								 WHERE pls.Point_de_livraison = '".$NoPL."' 
								 AND facture".$suf."_pls.pl_id = pls.id 
								 AND facture".$suf.".id = facture".$suf."_pls.facture".$suf2."_id");
			$raws = $query->result_array();
			
			if ($raws)
			{
				foreach ($raws as $raw)
				{
					$date=strtotime($raw['Date_index']);
					//$date = $this->dateSTAMP($raw['Date_index'],'/');	
					//echo $date; die;			
					if (($date >= $this->limitDebut)&&($date <= $this->limitFin))
					{
						$miniday = $this->make_day($raw);
						
						for($i=0;$i<$raw['Nb_jours'];$i++)
						{
							if ($date >= $this->limitDebut)
							{
								$this->calendar[$date] = $miniday;
								$this->calendar[$date]['date'] = date('d/m/y',$date);
								$date = strtotime("-1 day",$date);
							}
							else break;
						}
					}
				}
				
				if ($this->calendar)
				{
					//tri la table et recupere les dates de debut et fin
					krsort($this->calendar);
					reset($this->calendar);
					$this->fin = key($this->calendar);
					end($this->calendar);
					$this->debut = key($this->calendar);
					
					//completer les dates vides 
					$miniday = $this->calendar[$this->fin];
					$miniday['Fake'] = true;
					$this->Days_real = 0;
					$this->Days_fake = 0;
					
					for($i = $this->limitFin; $i >= $this->limitDebut; $i = strtotime("-1 day",$i))
					{
						if ((isset($this->calendar[$i]))&&(!is_null($this->calendar[$i])))
						{
							$miniday = $this->calendar[$i];
							$miniday['Fake'] = true;
							$this->Days_real++;
						}
						else 
						{
							$miniday['date'] = date('d/m/y',$i);
							$this->calendar[$i] = $miniday;
							$this->Days_fake++;
						}
					}
					krsort($this->calendar);
					//enregistre le nbr de jours dans l'histo
					$this->Days = $this->Days_real + $this->Days_fake;
					
					//echo $this->dateDISP($this->debut).' - '.$this->dateDISP($this->fin).' - '.round($this->Days_fake*100/$this->Days).' % fake<br>';
				}
			}
		}
	}
	
	function make_day($raw,$type)
	{
		//TO OVERWRITE !
		return null;
	}
	
	function getQuantity($nom,$jours = 1,$ref_date = null,$offset=0)
	{
		//ref_date : date considérée pour le calcul
		if ($ref_date == null) $ref_date = $this->Date_c;
		if ($offset > 0) $ref_date = strtotime("-".$offset." days",$ref_date);
		
		$ret = 0;
		
		for($i = 1; $i <= $jours; $i++)
		{
			if ($ref_date < $this->limitDebut) 
			{
				$ret += $this->calendar[$this->limitDebut][$nom]*($jours+1-$i);
				$i = $jours+1;
			}
			else if ($ref_date > $this->limitFin)
			{
				$nd = min($jours,floor(($ref_date - $this->limitFin)/86400));
				$ret += $this->calendar[$this->limitFin][$nom]*$nd;
				$ref_date = strtotime("-".$nd." day",$ref_date);
				$i += $nd;
			} 
			else 
			{
				if (!isset($this->calendar[$ref_date])) ;//echo '.';
				else $ret += $this->calendar[$ref_date][$nom];
				//if ($nom == 'KWh') echo $this->calendar[$ref_date][$nom].'<br>';
				$ref_date = strtotime("-1 day",$ref_date);
			}
		}
		
		return $ret;
	}
}

//HISTO EAU
class histoEAU_model extends histo_model
{		
	function __construct($NoPL,$histoSize,$Date_c=false)
	{
		parent::__construct($NoPL,'EAU',$histoSize,$Date_c);
	}
	
	function make_day($raw)
	{
		$j = $raw['Nb_jours'];
		$ans = null;
		
		$ans['Fake'] = false;
		
		if (($raw['Consommation_mensuelle'] != '')&&($raw['Consommation_mensuelle'] != 'Consommation mensuelle'))
		{
			//Conso m3
			$ans['m3'] = round($this->num($raw['Consommation_mensuelle'])*100/$j)/100;
			
			//Puissance souscrite
			$ans['PS'] = $this->num($raw['Puisance_souscrite']);
			
			//Montant Facture (Total net)
			$ans['MF'] = round($this->num($raw['Montant_net'])*100/$j)/100;
 			
			//Cout moyen m3
			$ans['CM3'] = round($ans['MF']*100/$ans['m3'])/100;
 			
			//Taux de charge du contrat : (m3/J) / (m3 souscrite x 10h) en %  -> 100% signifie utilise son contrat à fond pendant 10h/J
//			$ans['TC'] = round($ans['m3']*100/($this->num($raw['Puisance souscrite'])*10));
		}
		
		return $ans;
	}
}

//CLASSE HISTO BT : FACTURES BT
class histoBT_model extends histo_model
{	
	function __construct($NoPL,$histoSize,$Date_c=false)
	{
		parent::__construct($NoPL,'BT',$histoSize,$Date_c);
	}
	
	//la fabrication de chaque jour avec différents ratio journaliers
	function make_day($raw)
	{
		$j = $raw['Nb_jours'];
		$ans = null;
				
		$ans['Fake'] = false;
				
		if (($raw['Consommation_mensuelle'] != '')&&($raw['Consommation_mensuelle'] != 'Consommation mensuelle'))
		{
			//Conso KWh
			$ans['KWh'] = round($this->num($raw['Consommation_mensuelle'])*100/$j)/100;
			$ans['MWhBT'] = round($ans['KWh']/10)/100;  //BT
			$ans['MWhMTHP'] = 0; //MT Hors Pointe
			$ans['MWhMTP'] = 0;  //MT Pointe
			
			//Puissance souscrite
			$ans['PS'] = $this->num($raw['Puisance_souscrite']);
			
			//Puissance atteinte
			$ans['PA'] = 0; //////////////////Les BT ne peuvent dépasser la puissance souscrite !
			
			//Montant Facture (Total net)
			$ans['MF'] = round($this->num($raw['Montant_net'])*100/$j)/100;
					
			//Cout moyen KWh
			if ($ans['KWh'] > 0) $ans['CK'] = round($ans['MF']*100/$ans['KWh'])/100;
			else $ans['CK'] = 0;
			
			//Taux de charge du contrat : (KWh/J) / (P souscrite x 10h) en %  -> 100% signifie utilise son contrat à fond pendant 10h/J
//			$ans['TC'] = round($ans['KWh']*100/($this->num($raw['Puisance souscrite'])*10));
		}
		
		return $ans;
	}
}

//HISTO MT
class histoMT_model extends histo_model
{		
	function __construct($NoPL,$histoSize,$Date_c=false)
	{
		parent::__construct($NoPL,'MT',$histoSize,$Date_c);
	}
	
	function make_day($raw)
	{
		$j = $raw['Nb_jours'];
		$ans = null;
		
		$ans['Fake'] = false;
		
		if ((($raw['Conso_Pointe'] != '')||($raw['Conso_Hors_Pointe'] != ''))&&($raw['Conso_Pointe'] != 'Conso Pointe'))
		{
			//Conso KWh
			$ans['KWh'] = round(($this->num($raw['Conso_Pointe'])+$this->num($raw['Conso_Hors_Pointe']))*100/$j)/100;
			$ans['MWhBT'] = 0;  //BT
			$ans['MWhMTHP'] = round($this->num($raw['Conso_Hors_Pointe'])/$j/10)/100; //MT Hors Pointe
			$ans['MWhMTP'] = round($this->num($raw['Conso_Pointe'])/$j/10)/100;  //MT Pointe
			
			
			//Puissance souscrite
			$ans['PS'] = $this->num($raw['Puisance_souscrite']);
			
			//Puissance atteinte
			$ans['PA'] = $this->num($raw['Conso_PA']);
			
			//Montant Facture (Total net)
			$ans['MF'] = round($this->num($raw['Montant_net'])*100/$j)/100;
			
			//Cout moyen KWh
			if ($ans['KWh'] > 0) $ans['CK'] = round($ans['MF']*100/$ans['KWh'])/100;
			else $ans['CK'] = 0;
			
			//Taux de charge du contrat : (KWh/J) / (P souscrite x 10h) en %  -> 100% signifie utilise son contrat à fond pendant 10h/J
//			$ans['TC'] = round($ans['KWh']*100/($this->num($raw['Puisance souscrite'])*10));
		}
					
		return $ans;
	}
}
?>
