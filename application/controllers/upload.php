<?php

require_once ("progressbar.php");

class Upload extends Controller {


var $nombres_lignes;
var $nombres_colonnes;	
var $nombres_requetes;
	
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('form', 'url'));
	}

	function index()
	{
		$this->viewlib->view('upload_form', array('error' => ' ' ));
	}

	function do_upload($table)
	{
		$page = $this->viewlib->view('import/upload_progress',null,false,'',true);
		echo $page;
		
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = '*';
		$config['max_size']	= '10000';
		$this->load->library('upload', $config);
		

		if ( ! $this->upload->do_upload())
		{
			$this->error_display();
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
			
			$file_name=$data['upload_data']['file_name'];
			
			//import de la facture dans la base de donnee
			$entries = $this->xls_to_db($file_name, $table);
			$this->historique_upload($file_name);	
			echo '<script>window.location = "'.site_url("import/index/".$entries).'"</script>';
		}
	}
	
	function xls_to_db($file_name, $table)
	{
	 	$this->nombres_requetes=0;
	 	$prod=$this->parseExcel('./uploads/'.$file_name, $table);

	 	if (is_array($prod)){
		 	//Ecriture de la requete sql	
			$requete_sql='';
			echo count($prod).' factures en cours d\'importation.. Cela peut prendre quelques minutes !';
			for ($i = 1; $i <= $this->nombres_lignes-1; $i++) {
				$values='(';
				for ($j = 0; $j <= $this->nombres_colonnes; $j++) {
					$values=$values.$prod[$i][$j].',';
				}
				$values = substr($values, 0, -2).')';
				$requete_sql="REPLACE INTO `".$table."` VALUES ".$values.'; ';
				//Verifie PL non nul et n° facture numerique
				$PL=substr($prod[$i][8], 1, -1);
				$nofacture=substr($prod[$i][2], 1, -1);
				if (($prod[$i][8]!="") and (is_numeric($nofacture))){
					//execute la requete
					$this->nombres_requetes++;
					$this->db->query($requete_sql);			
				}
				//avancement barre de progression
				$pourcentage=round(100*($i/$this->nombres_lignes),0);
				if ($pourcentage > $pn)
				{
					$pn = $pourcentage;
					ProgressBar($pn);
				}
			}
			ProgressBar(100);
			return $this->nombres_requetes;
		}
	}
	
	function parseExcel($excel_file_name_with_path, $table)
	{
		
		$data = new spreadsheetexcelreader();
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		$data->read($excel_file_name_with_path);
		
		$this->nombres_colonnes=$data->sheets[0]['numCols'];
		if ($table=='Conso_MT') $this->nombres_colonnes=56;
		elseif ($table=='Conso_BT') $this->nombres_colonnes=26;
		$this->nombres_lignes=$data->sheets[0]['numRows'];
		
		//Si la différence entre le nombre de colonne effectif et attendu est supérieur à 10, erreur
		if (abs($this->nombres_colonnes-$data->sheets[0]['numCols'])>10){
			$this->error_display('Le nombre de colonnes du fichier est incoh&eacute;rent. Etes-vous s&ucirc;r d\'avoir s&eacute;lectionn&eacute; le bon type (BT/MT)?');
			return FALSE;
		}
		
		for ($i = 1; $i <= $this->nombres_lignes-2; $i++) {
			for ($j = 1; $j <= $this->nombres_colonnes; $j++) {                   
				//formatage des dates de dd/mm/yyyy to yyyy-mm-dd
				if (preg_match('#^([0-9]|[0,1,2][0-9]|3[0,1])/([\d]|[0,1][0-9])/\d{4}$#', $data->sheets[0]['cells'][$i][$j]))
				{
					$date=$data->sheets[0]['cells'][$i][$j];
					$date_array = explode("/",$date); // split the array
					$var_day = $date_array[0]; //day seqment
					$var_month = $date_array[1]; //month segment
					$var_year = $date_array[2]; //year segment
					$date = "$date_array[2]-$date_array[1]-$date_array[0]";
					$data->sheets[0]['cells'][$i][$j]=$date;
				}
				//enleve les " à l'interieur des champs
				$search = array ("/\"/");
				$replace = array ('');
				$data->sheets[0]['cells'][$i][$j] = preg_replace($search, $replace, $data->sheets[0]['cells'][$i][$j]);
				//entoure les champs de ""
				$data->sheets[0]['cells'][$i][$j]="\"".$data->sheets[0]['cells'][$i][$j]."\"";
				$product[$i-1][$j-1]=$data->sheets[0]['cells'][$i][$j];
			}
		}
		return $product;
	}
	
	function historique_upload($file_name){
		$requete_sql="INSERT INTO `upload` (`id`,`nom_fichier`,`date_creation`) VALUES (NULL,\"".$file_name."\", CURDATE());";
		$this->db->query($requete_sql);		
	}
	
	function error_display($texterror=null){
		$attributes = array('id' => 'errform');
		echo form_open('import/index/-1',$attributes);
		if (!is_null($texterror)){
				echo form_hidden('error',$texterror);
		}
		else{
			echo form_hidden('error',$this->upload->display_errors());
		}
		echo form_close();
		echo '<script>document.getElementById("errform").submit();</script>';
	}
	
}
?>
