<?php

# override the default TCPDF config file
if(!defined('K_TCPDF_EXTERNAL_CONFIG')) {	
	define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
}
	
# include TCPDF
require(APPPATH.'config/tcpdf'.EXT);
require_once($tcpdf['base_directory'].'/tcpdf.php');



/************************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/
class pdf extends TCPDF {
	
	protected $filenamepdf;
	
	/**
	 * TCPDF system constants that map to settings in our config file
	 *
	 * @var array
	 * @access private
	 */
	private $cfg_constant_map = array(
		'K_PATH_MAIN'	=> 'base_directory',
		'K_PATH_URL'	=> 'base_url',
		'K_PATH_FONTS'	=> 'fonts_directory',
		'K_PATH_CACHE'	=> 'cache_directory',
		'K_PATH_IMAGES'	=> 'image_directory',
		'K_BLANK_IMAGE' => 'blank_image',
		'K_SMALL_RATIO'	=> 'small_font_ratio',
	);
	
	
	/**
	 * Settings from our APPPATH/config/tcpdf.php file
	 *
	 * @var array
	 * @access private
	 */
	private $_config = array();
	
	
	/**
	 * Initialize and configure TCPDF with the settings in our config file
	 *
	 */
	function __construct() {
		
		# load the config file
		require(APPPATH.'config/tcpdf'.EXT);
		$this->_config = $tcpdf;
		unset($tcpdf);
		
		
		
		# set the TCPDF system constants
		foreach($this->cfg_constant_map as $const => $cfgkey) {
			if(!defined($const)) {
				define($const, $this->_config[$cfgkey]);
				#echo sprintf("Defining: %s = %s\n<br />", $const, $this->_config[$cfgkey]);
			}
		}
		
		# initialize TCPDF		
		parent::__construct(
			$this->_config['page_orientation'], 
			$this->_config['page_unit'], 
			$this->_config['page_format'], 
			$this->_config['unicode'], 
			$this->_config['encoding'], 
			$this->_config['enable_disk_cache']
		);
		
		
		# language settings
		if(is_file($this->_config['language_file'])) {
			include($this->_config['language_file']);
			$this->setLanguageArray($l);
			unset($l);
		}
		
		# margin settings
		$this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);
		
		# header settings
		$this->print_header = $this->_config['header_on'];
		#$this->print_header = FALSE; 
		$this->setHeaderFont(array($this->_config['header_font'], '', $this->_config['header_font_size']));
		$this->setHeaderMargin($this->_config['header_margin']);
		$this->SetHeaderData(
			$this->_config['header_logo'], 
			$this->_config['header_logo_width'], 
			$this->_config['header_title'], 
			$this->_config['header_string']
		);
		
		# footer settings
		$this->print_footer = $this->_config['footer_on'];
		$this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
		$this->setFooterMargin($this->_config['footer_margin']);
		
		# page break
		$this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);
		
		# cell settings
		$this->cMargin = $this->_config['cell_padding'];
		$this->setCellHeightRatio($this->_config['cell_height_ratio']);
		
		# document properties
		$this->author = $this->_config['author'];
		$this->creator = $this->_config['creator'];
		
		# font settings
		#$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);
		
		# image settings
		$this->imgscale = $this->_config['image_scale'];
		
		#default file name
		$this->SetFileNamePDF();
	}
	
	function SetFileNamePDF($fn='')
	{
		if($fn != '') $this->filenamepdf = $fn.'.pdf';
		else $this->filenamepdf = 'default.pdf';
	}
	
	function AddPagePDF($newgroup = 0) 
	{
		//nouveau groupe avant nouvelle page
		if ($newgroup == 1) $this->startPageGroup();
		
		// add a page
		$this->AddPage();
		
		//nouveau groupe après nouvelle page
		if ($newgroup == -1) $this->startPageGroup();
	}
	
	function ClosePDF()
	{
		$this->lastPage();
		
		//clean directory
		$docs = NULL;
		list(,$filetype) = explode('.',$this->filenamepdf);
		
		$dir = opendir($this->_config['output_directory']);
		$cnt = 0; 
		while ($f = readdir($dir)) 
		{
			if(is_file($this->_config['output_directory'].$f)) 
			{
				if ($f == $this->filenamepdf) unlink($this->_config['output_directory'].$f);
				else
				{
					$filename = explode('.',$f);
					if($filename[1] == $filetype)
					{      
						$cnt++;
						$docs[filemtime($this->_config['output_directory'].$f)] = $this->_config['output_directory'].$f;
					}
				}
			}
		}
		closedir($dir);
		
		
		if($cnt >= $this->_config['max_doc_type'])
		{
			ksort($docs);
			foreach($docs as $date=>$file)
			{
				if(is_file($file)) 
				{
					unlink($file); //del file 
					$cnt--;
					if($cnt < $this->_config['max_doc_type']) break;
				}
			}
		}
		
		//OUTPUT 
		$name = $this->filenamepdf;
		$this->Output($this->_config['output_directory'].$name, 'F');
		return $this->DisplayPDF($name);
	}
	
	function DisplayPDF($name)
	{
		return '<script type="text/javascript">
				impression = window.open("'.base_url().$this->_config['output_directory'].$name.'", "Travaux d\'impression", "location=no, width=600, height=600, menubar=no, status=no, scrollbars=yes, directories=no,  toolbar=no");
				impression.focus();
			</script>';
	}
	
	function makeList($liste,$style='inline',$print=true) //style: inline or list
	{
		if ($style == 'list') 
		{
			$ln = 0;
			foreach($liste as $n=>$v) $ln = max($ln,(strlen($n.' :')*2));
			$lv = 190 - $ln; 
		}
		
		$k = 0;
		$html = '<table>';
		if ($style == 'inline') $html .= '<tr>';
		foreach($liste as $n=>$v) 
		{
			if ((trim($v) != '')or(trim($n) == ''))
			{	
				if ($style == 'inline')
				{
					$ln = (strlen($n.': ')*1.9); 
					$lv = (strlen($v.'  ')*1.9);
					if ($k == 0) $kmem = 10;
					if(($k+$ln+$lv) > 191) {$html .= '</tr></table><table><tr><td style="width:'.$kmem.'mm"></td>'; $k=$kmem;}
				}
				
				//decortique et recombine les saut à la ligne
				$valb = explode('<br />',$v); $kb = $k; $v = '';
				foreach($valb as $vb) 
				{
					$lvb = 2+strlen($vb)*2;
					if (($kb+$lvb+$ln) > (200)) {$br='<br />'; $kb = $k;}
					else $br = ' ';
					$v .= $br.$vb; 
					$kb += $lvb;
				}
											
				if ($style == 'list') $html .= '<tr>';
				
				if (trim($n) != '') $n .= ':';
				$html.= '<td style="width:'.$ln.'mm"><strong>'.$n.' </strong></td><td style="width:'.$lv.'mm">'.$v.' </td>';
				
				if ($style == 'inline') $k += $ln+$lv;
				else if ($style == 'list') $html .= '</tr>';
			}
		}
		if ($style == 'inline') $html .= '</tr>';
		$html .= '</table>';
		
		if ($print) $this->writeHTML($html);
		else return $html;
	}
	
	function makeTable($table,$print=true,$clean_empty=false,$max_isize=false,$all_resize=false,$align_method=false)
	{
		/* Structure attendue pour $table :
		*	$table['nom de la ligne][$i] = valeur pour la colonne $i;
		*
		*	exemple :
		*	$table = array (
		*					' '			=> array ('Puissance', 'Nombre	', 'HS	'),
		*					'Site 1'	=> array ('35 kW	', '42		', '10% '),	
		*					'Site 2'	=> array ('8 kW 	', '2 		', ' 1% '),
		*					'Site 3'	=> array ('123 kW	', '105     ', '25%	')
		*					);
		*
		*	la taille de la premiere colonne est adaptée au contenu (Site 1, Site 2, ...)
		*	la taille peut être limitée par $max_isize
		*	les autres cellules se partage identiquement l'espace restant
		*
		*	pour ne pas afficher la première colonne (nom de la ligne : Site 1, Site 2, ...)
		*   mettre le nom de la première ligne à 0 (ci dessus il vaut actuellement ' ')
		*
		*	ARGS :
		*	$print => directly print in the pdf or return table
		*	$clean_empty => remove empty lines ?
		*	$max_isize => largeur max des colonne en auto resize
		*   $all_resize => autodimmensionne toutes le colonnes ou seulement la première
		*   $align method : tableau avec les regle d'alignement du texte pour chaque colonne :left center right
		*/
		
		$nodata = false;
		
		if (key($table) === 0) $display_firstcolumn = false;
		else $display_firstcolumn = true;
		
		$sizH = array(0);
		$cnt = 1;
		foreach($table as $r=>$row) 
		{
			//taille idéale première colonne
			$sizH[0] = max((strlen($r)*1.8),$sizH[0]);
			
			//nombre max de colonne
			if (is_array($row)) $cnt = max($cnt,count($row));
			
			//calcul la taille idéale pour chaque colonne
			if ($all_resize)
			{
				$iH = 1;
				
				foreach ($row as $c=>$col) 
				{
					if (!isset($sizH[$iH])) $sizH[$iH] = 0;
					$sizH[$iH] = max((strlen($col)*1.8+2),$sizH[$iH]); 
					$iH++;
				}
			}			
		}
		if ($max_isize) foreach ($sizH as $iH=>$v) $sizH[$iH] = min($max_isize,$v);
		
		//print_r($sizH);die;
		
		if ($clean_empty)
		foreach ($table as $r=>$row)
		{
			if (is_array($row))
			{
				$acc = 0;
				foreach($row as $val) 
				{
					if (is_numeric($val)){if ($val != 0) $acc++;}
					else if ((trim($val) != '')or(trim($r) == '')) $acc ++;
				}
				if ($acc == 0) unset ($table[$r]);
			}
			
			if (count($table) <= 1) $nodata = true;
		}
		
		if (!$nodata)
		{
			$i = true; //first line : strong !
			$out = '<table>';
			foreach($table as $r=>$row)
			{
				$out .= '<tr>';
				if($display_firstcolumn) $out .= '<td width="'.$sizH[0].'mm" style="border-bottom:1px solid black"><strong><font size="8">'.$r.'</font></strong></td>';
				
				$iH = 1;
				if (is_array($row))
				{	
					$iH = 1;
					foreach ($row as $ro)
					{
						$siz = (($i)&&(trim($ro) == ''))?'width:3mm;':'';
						if (($siz == '')&&(isset($sizH[$iH]))) $siz = 'width:'.$sizH[$iH].'mm;';
						$align =  (($align_method)&&(isset($align_method[$iH])))?'text-align:'.$align_method[$iH].';':'';
						
						
						$out .= '<td style="border-bottom:1px solid black;'.$siz.$align.'">';
						$out .= ($i)?'<strong>':'';
						$out .= $ro;
						$out .= ($i)?'</strong>':'';
						$out .= '</td>';
						$iH++;
					}
				}
				else 
				{
					$out .= '<td style="border-bottom:1px solid black" colspan="'.$cnt.'">';
					$out .= ($i)?'<strong>':'';
					$out .= $row;
					$out .= ($i)?'</strong>':'';
					$out .= '</td>';
				}
				
				$out .= '</tr>';
				$i= false;
			}
			$out .= '</table>';
		}
		else $out = '<i>aucune donnée</i>';
		
		if ($print) $this->writeHTML($out);
		else return $out;
	}
	
	function makeTitle($title)
	{
		$this->SetFont('helvetica','B',13);
		$this->Cell(0, 10, $title, 1, 1, 'C');
		$this->SetFont('helvetica','',9);
	}
	
	function makeSubTitle($title)
	{
		$this->Cell(0, 5, ' ', 0, 1);
		$this->SetFont('helvetica','',11);
		$this->Cell(0, 5, $title, 'B', 1, 'L');
		$this->SetFont('helvetica','',9);
		$this->Write( 1, ' ', '', 0, '', true);
	}
}