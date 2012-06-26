<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once ('gwikid_model.php');

class Crudentry_model extends Gwikid_model
{
	private $valid;
	private $table;
	private $fields;
	private $fields_details;
	private $index;
	protected $wantedID;
	private $form_data;
	protected $RW;
	
	public $form;
	public $data;
	public $data_extra;
	
	public $action;
	public $action_previous;
	
	function __construct($enable_action=true)
	{
		parent::__construct();
		$this->valid = false;
		
		//RIGHTS : 1 = LOGGED, --customer side--, 5 = CUSTOMER admin, --developper side--,10 = ROOT admin
		$this->RW->{'Read'} = 1; 
		$this->RW->{'Insert'} = 1;
		$this->RW->{'Update'} = 1;
		
		$this->action = $this->input->post('_action');
		if (($enable_action)and($this->action))
		{	
			$this->action_previous = $this->session->userdata('action_previous');
			$this->session->set_userdata('action_previous',$this->action);
		}
		
		$this->wantedID = NULL;
	}
	
	//SELECT TABLE and LOAD if ID given (or clean for new site)
	function init($tbl,$id=NULL)
	{
		$this->table = $tbl;
		$this->fields_details = $this->db->field_data($this->table);
		foreach($this->fields_details as $fi) $this->fields[] = $fi->name;
		
		$this->index = 'id'.$this->table;
		
		if (in_array($this->index,$this->fields)) $this->valid = true;
		
		//creation des champs par defaut correspondant au champ de la DB
		foreach($this->fields_details as $fi) $this->init_field($fi->name,'none');
			
		if (is_NULL($id)) $this->clean();
		else $this->load($id);
	}
	
	function valid()
	{
		return $this->valid;
	}
		
	//EMPTY ENTRY
	function clean()
	{
		//refresh all stored datas
		$this->data = $this->objectarray->fromValues($this->fields);
	}
	
	//LOAD BY ID
	function load($id)
	{
		$this->db->where($this->index, $id);
		$this->db->limit(1);
		$query = $this->db->get($this->table);
		
		if ($this->guard('Read'))
		{
			if ($query->row()) 
			{
				//unset ($this->data);
				$this->data = $this->objectarray->full($query->row());
				$this->form_data = $this->objectarray->full($query->row());
				
				return true;
			}
			else 
			{
				$this->clean();
				$this->wantedID = $id;
			}
		}
		else $this->session->set_userdata('info', 'Vous n\'avez pas les droits pour consulter cette entrée !');
		
		return false;
	}
	
	// SAVE ME
	function save($use_histo = true)
	{
		//new entry (id is NULL) : insert
		if (!($this->id() > 0)) 
		{
			if ($this->guard('Insert'))
			{
				if ((in_array('DateCreation',$this->fields))&&(is_NULL($this->data->DateCreation))) $this->setdata('DateCreation',date("Y-m-d"));
				
				$this->db->insert($this->table, $this->data);
				$this->data->{$this->index} = $this->db->insert_id();
				
				$this->save_modules();
				if (is_NULL($this->wantedID)) $this->wantedID = $this->id();
			}
			else $this->session->set_userdata('info', 'Vous n\'avez pas les droits pour enregistrer une nouvelle entrée !');
		}
		else if ($this->guard('Update'))
		{
			//Desactiver l'histo si c'est la première validation d'un champ
			if ((in_array('Valide',$this->fields))&&($this->data->Valide != 1)) 
			{
				$use_histo = false;
				$this->data->Valide = 1;
			}
			
			//Desactiver l'histo si la creation du champs date de moins de 30 jours
			if ((in_array('DateCreation',$this->fields))&&(strtotime($this->data->DateCreation) >= strtotime("-30 days",time()))) 
			{
				$use_histo = false;
			}
			
			//creation historique, si la table comprend un champs 'DateFin', celui ci est utilisé, et une nouvelle entrée est créée
			if ((in_array('DateFin',$this->fields))&&($use_histo)&&($this->data->DateFin == NULL)) 
			{
				//retrieve old version, close it, and save it with a new id (so the active id stay the same)
				$oldRow = new Crudentry_model();
				$oldRow->init($this->table,$this->id());
				$oldRow->setdata($this->index,NULL);
				$oldRow->setdata('DateFin',date('Y-m-d'));
				$oldRow->save();
				unset ($oldRow);
				
				//save this new version with a new DateCreation value  but with the same id.
				$this->setdata('DateCreation',date('Y-m-d'));
				
				$this->db->where($this->index, $this->id());
				$this->db->update($this->table, $this->data);
				
				$this->save_modules();
				/*
				$this->db->where($this->index, $this->id());
				$this->db->update($this->table, array('DateFin' => date('Y-m-d')));
				
				$this->setdata('DateCreation',date('Y-m-d'));
				$this->data->{$this->index} = NULL;
				$this->save();
				*/
			}
			//existing entry : update
			else
			{
				$this->db->where($this->index, $this->id());
				$this->db->update($this->table, $this->data);
				
				$this->save_modules();
			}
			$this->editing(false);
			if (is_NULL($this->wantedID)) $this->wantedID = $this->id();
		}
		else $this->session->set_userdata('info', 'Vous n\'avez pas les droits pour modifier cette entrée !');
	}
	
	// delete
	function remove($use_histo=true)
	{
		//Desactiver l'histo si la creation du champs date de moins de 3 jours
		if ((in_array('DateCreation',$this->fields))&&(strtotime($this->data->DateCreation) >= strtotime("-3 days",time()))) $use_histo = false;
		
		if (($this->guard('Update'))&&(!$this->is_new()))
		{
			//creation historique, si la table comprend un champs 'DateFin', celui ci est utilisé
			if ((in_array('DateFin',$this->fields))&&($use_histo)&&($this->data->DateFin == NULL)) 
			{
				$this->db->where($this->index, $this->id());
				$this->db->update($this->table, array('DateFin' => date('Y-m-d')));
				$this->clean();
			}
			//real delete
			else
			{
				$this->db->where($this->index, $this->id());
				$this->db->delete($this->table);
				$this->clean();
			}
		}
	}
	
	function id()
	{
		return $this->data->{$this->index};
	}
	
	function editing($switch=NULL)
	{
		if ($switch !== NULL) $this->session->set_userdata($this->table.'_editing_'.$this->id(),$switch);
		else return $this->session->userdata($this->table.'_editing_'.$this->id());
	}
	
	function guard($action = false)
	{
		return true;
		
		if (!$action) return ($this->simplelogin->group() > 0); 
		else if ($this->RW->{$action} <= $this->simplelogin->group()) return true;
		else $this->session->set_userdata('info', 'Vous n\'avez pas les droits pour effectuer cette action !');
		
		return false;
	}
	
	//Create default field object for data column 
	function init_field($name,$type='none',$default=NULL)
	{
		if ($name != '') $this->form[$name] = new Formfield($name,$type);
		$this->setformdata($name,$default);
	}
	
	function add_form($id,$default=NULL,$suffix=false)
	{
		if ((!$default)&&($suffix)) 
		{
			$this->init_field($id.'_'.$suffix,'none',$this->getformdata($id)); //add a suffix and use the default value from Nosuffix data
			return $this->form[$id.'_'.$suffix];
		}
		else 
		{
			$this->init_field($id,'none',$default); //classic add new field
			return $this->form[$id];
		}
	}
	
	function rem_form($id)
	{
		if (isset($this->form[$id])) unset($this->form[$id]);
	}
	
	//met tout les champs en caché
	function fields_method($method='hide',$name=null)
	{
		//on fourni le nom du champs et la method
		if (!is_null($name)) $this->form[$name]->{$method}();
	
		//si method donnée directement : appliquer à tous les fields!
		else if (!is_array($method)) foreach($this->form as $name=>$Ffield) $Ffield->{$method}();
		
		//sinon method est un tableau associatif avec nom du champ => method
		else foreach($method as $name=>$meth) $this->form[$name]->{$meth}();
		
		
	}
	
	//retourn tableau des fields pour injection formulaire  
	function u_formfields() //pour cet objet uniquement
	{
		//print_r(get_object_vars($this->form_data));
		$liste = array();
		foreach($this->form as $name=>$Ffield) 
		{			 
			$liste[$name] = $Ffield->get($this->getformdata($name));
		}
		
		return $liste;
	}
	
	//retourn tableau des fields pour injection formulaire  
	function formfields() //pour tous les objets (YO OVERWRITE !!)
	{
		$fields = $this->u_formfields();
		
		return $fields;
	}
	
	function checkfields()
	{		
		//print_r($_POST);die;
		$this->mix_down();
		
		foreach($this->form as $name=>$Ffield)
			if ($Ffield->show == 'form') 
			{
				if (isset($_POST[$name])&&(!is_array($_POST[$name]))) $_POST[$name] = trim(htmlspecialchars($_POST[$name])); //trim all !
				if ($this->input->post($name) == '*autre') $_POST[$name] = $_POST[$name.'_autre'];
				$this->form_validation->set_rules($name,$Ffield->label,$Ffield->rules); //applique les regles AVANT le deformatage..				
				$this->setformdata($name,$Ffield->unformat($this->input->post($name))); // sauvegarde le retour formulaire comme nouvelle valeur par defaut (to repopulate fields))
			}

		if($this->form_validation->run())
		{
			//si le formulaire est validé on enregistre dans le modele les nouvelle valeurs
			foreach($this->form as $name=>$Ffield)
				if ($Ffield->show == 'form') 
				{
					//if ($this->getformdata($name) == '*autre') $this->setdata($name,$this->getformdata($name.'_autre'));
					$this->setdata($name,$this->getformdata($name));
				}	
		//echo $this->data->Annee; die;
			// on enregistre les donnée des modules complémentaires
			if (isset($this->modules))
			foreach($this->modules as $table=>$mods)
				foreach($mods as $i=>$mod)
					foreach($mod->fields as $fi)
						if (isset($this->data_extra->{$table.'_'.$fi.'_'.$i})) $mod->setdata($fi,$this->data_extra->{$table.'_'.$fi.'_'.$i});
			
			
			$this->mix_up();
			return true;
		}
		return false;
	}
	
	function mix_down() //à appliquer pour traiter les donnée, avant validation
	{
		//fonction vierge par defaut.
		// a ecraser dans les modèles custom
	}
	
	function mix_up() //à appliquer pour traiter les donnée, après validation, avant enregistrement
	{
		//fonction vierge par defaut.
		// a ecraser dans les modèles custom
	}
	
	function getdata($name)
	{
		return $this->data->{$name};
	}
	function setdata($name,$value)
	{
		if(in_array($name,$this->fields)) $this->data->{$name} = $value;
		else $this->data_extra->{$name} = $value;	
	}
	
	function setformdata($name,$value)
	{
		$this->form_data->{$name} = $value;
		
		return $this;
	}
	
	function getformdata($name)
	{
		return $this->form_data->{$name};
	}
	
	//permet de charger des sous composant relatif à celui ci dans d'autres tables
	function load_module($table,$size=false)
	{
		$this->modules[$table] = array();
		$i = 0;
		
		if (!$this->is_new())
		{
			$this->db->select('id'.$table);
			if ($size) $this->db->limit($size);
			$this->db->where($this->index,$this->id());
			$query = $this->db->get($table);
			
			foreach($query->result() as $row)
			{
				$this->modules[$table][$i] = new Crudentry_model();
				$this->modules[$table][$i]->init($table,$row->{'id'.$table});				
				$i++;
			}
		}

		if (!$size) $size = 4;
		
		for($k=$i;$k<$size;$k++)
		{
			$this->modules[$table][$i] = new Crudentry_model();
			$this->modules[$table][$i]->init($table);
			$i++;
		}

	}
	
	function save_modules()
	{
		// on enregistre les donnée des modules complémentaires
		if (isset($this->modules))
		foreach($this->modules as $table=>$mods)
			foreach($mods as $i=>$mod)
			{
				$mod->setdata($this->index,$this->id());
				$mod->save();
			}
	}
	
	function is_new()
	{
		if (is_numeric($this->id())) return false;
		else return true;
	}
}

class Formfield
{
	private $name;
	private $type;
	private $field;
	private $format;

	private $size_or_val;
	private $size2;
	private $js;
	
	private $last_default;
	
	public $show;
	public $rules;
	public $label;
	
	function Formfield($name,$type='none')
	{
		$this->format = '';
		$this->rules = '';
		$this->name = $name;
		$this->label = $name;
		$this->show = 'hide';
				
		$this->set($name,$type);
	}
	
	function set($label,$type,$size_or_val=false,$size2=false,$js=NULL)
	{		
		$this->size_or_val = $size_or_val;
		$this->size2 = $size2;
		$this->js = $js;
		$this->label = $label;
		
		$this->type = $type;
		
		//DEFAULT FORMATERS depending on input type
		if ($type == 'date') $this->add_format('date');
		if (($type == 'id_select')||($type == 'id_select_0')||($type == 'id_select_E')) $this->add_format('idlist');
		if ($type == 'mlist_text') $this->add_format('mlist_text');
		if ($type == 'mlist_text_autre') $this->add_format('mlist_text');
		if ($type == 'mlist_id') $this->add_format('mlist_id');
		if (($type == 'check')||($type == 'yesno')) $this->add_format('check');
		if (($type == 'check1')) $this->add_format('check1');
		if ($type == 'textarea') $this->add_format('nbr');
		if ($type == 'flag') $this->add_format('flag');
		
		return $this;
	}
	
	function hide() //renvoi rien
	{
		$this->show = 'hide';
	}
	
	function vhide() //renvoi *hide
	{
		$this->show = 'Vhide';
	}
	
	function field() //renvoi le field
	{
		$this->show = 'form';
	}
	
	function printer() //renvoi la version print (pdf ready)
	{
		$this->show = 'print';
	}
	
	function display() //renvoi valeur du champ
	{
		$this->show = 'display';
	}
	
	function get($default='*NULL',$method=false)
	{
		//if ($this->name == 'Commentaire_1') echo $this->name.'- '.$this->show.'- '.$this->type.'<br>';
		
		if ($default != '*NULL') $this->last_default = $default;
		if ($method != false) $this->show = $method;
		
		//format default value to display, but don't save it
		$default = $this->format($this->last_default);
		
		if (($this->show == 'display')||($this->show == 'print')) return $default;
		else if ($this->show == 'hide') return '';
		else if ($this->show == 'Vhide') return '*hide';
		else if ($this->show == 'form')
		{
			$this->make($default);
			return $this->field;
		}
	}
	
	function make($default=NULL)
	{
		if ($this->last_default != $default) $this->last_default = $default;
		
		$field = NULL;
		
		$data['name'] = $this->name;
		if (form_error($this->name)!= '') $this->js .= ' style="background-color:#FEE;border: 2px solid red;"';

		$type = $this->type;

		if ($type == 'text') 
		{
			if ($this->size_or_val) $data['size'] = $this->size_or_val;
			$field = form_input($data,$default,$this->js);
		}
		if ($type == 'date') 
		{
			if ($this->size_or_val) $data['size'] = $this->size_or_val;
			else $data['size'] = 8;
			$field = form_input($data,$default,$this->js);
		}
		else if ($type == 'hidden') 
		{
			$field = form_hidden($this->name,$default);
		}
		else if ($type == 'check') 
		{
			if ($default == 1) $default = true;
			else $default = false;
			$field = form_checkbox($data,1,$default,$this->js);
		}
		else if ($type == 'radio') 
		{
			$field = form_radio($data,$this->size_or_val,$default,$this->js);
		}
		else if ($type == 'yesno') 
		{
			$ye = false;
			$no = false;
			if ($default == 1) $ye = 1;
			else if (($default == 0)&&(!is_null($default))) $no = 1;
			$field = form_radio($data,1,$ye,$this->js).'oui '.form_radio($data,0,$no,$this->js).'non ';
		}
		else if ($type == 'nbr_list10') 
		{
			$arr = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10);
			$field = form_dropdown($this->name,$arr,$default,$this->js);
		}
		else if ($type == 'select') 
		{
			if (!is_array($this->size_or_val)) $this->size_or_val = array();
			$field = form_dropdown($this->name,$this->size_or_val,$default,$this->js);
		}
		else if ($type == 't_select_AE')
		{
			$table = $this->name;
			if (($this->size2)&&($this->size2!='v')&&($this->size2!='h')) $table = $this->size2;
			
			$obj =& get_instance();
			$obj->load->model('List_model','lister');
			
			$options = array(' '=>' ');
		
			foreach($obj->lister->get($table) as $i=>$v) $options[$v] = $v;
			$options['*autre'] = 'Autre';
			$js = 'onChange="if (this.value == \'*autre\') show(\''.$this->name.'_autre\'); else hide(\''.$this->name.'_autre\');"';
				
			if ((!in_array($default,$options))&&($default != '')) {$defAutre = $default; $default = '*autre';}
			else $defAutre = '';
			
			$field = form_dropdown($this->name,$options,$default,$js);
			
			$data = array(
				'name' => $this->name.'_autre',
				'id'	=> $this->name.'_autre'
				);
				
			if ($this->size_or_val) $data['size'] = $this->size_or_val;
			if ($defAutre == '') $data['style'] = "display:none;"; 
			if ($this->size2 == 'v') $field .= '<br />';
			
			$field .= form_input($data,$defAutre,$this->js);
		}
		else if (($type == 'id_select')||($type == 'id_select_E')||($type == 'id_select_0'))
		{
			if ($this->size_or_val) $nt = $this->size_or_val;
			else $nt = $this->name;
			
			$table = explode('id',$nt);
			if ($table[1] != '')
			{
				$obj =& get_instance();
				$obj->load->model('List_model','lister');
				
				$options = array();
				
				if ($type == 'id_select_E') $options[' '] = ' ';
				if ($type == 'id_select_0') $options[0] = ' ';
				
				foreach($obj->lister->get($table[1]) as $i=>$v) $options[$i] = $v;
				
				$field = form_dropdown($this->name,$options,$default,$this->js);
			}
		}
		else if (($type == 'mlist_text')||($type == 'mlist_text_autre')||($type == 'mlist_id'))
		{
			$type2 = explode('_',$type);
			
			$table = $this->name;
			if ($table != '')
			{
				$defA = explode('|',$default);
				
				$obj =& get_instance();
				$obj->load->model('List_model','lister');
				
				$options = array();
				$options = $obj->lister->get($table);
								
								
				$field .= '<table cellspacing="0" class="clean"><tr>';
				foreach ($options as $id=>$nom)
				{
					if ($type2[1] == 'text') $value = $nom;
					else if ($type2[1] == 'id') $value = $id;
					
					if (in_array($value,$defA)) {$check = true; foreach($defA as $k=>$v) if ($v == $value) unset($defA[$k]);}
					else $check = false;
					
					 $field .= '<td>'.form_checkbox($this->name.'[]',$value,$check,$this->js).'</td><td>'.$nom.'</td>';
				}
				
				if ($type2[2] == 'autre') 
				{					
					$value = '';
					foreach($defA as $v) 
					{
						if ((trim($v) != '')&&(!in_array($v,$options))) 
						{	
							if ($value != '') $value.=', '; 
							$value .= $v;
						}
					}
					
				 	$field .= '<td> &nbsp;&nbsp;/&nbsp;&nbsp; Autre : </td><td>'.form_input($this->name.'[Autre]',$value,$this->js).'</td>';
				}
				
				$field .= '</tr></table>';
			}
		}
		else if ($type == 'textarea') 
		{
			if ($this->size_or_val) $data['rows'] = $this->size_or_val;
			if ($this->size2) $data['cols'] = $this->size2;
			$field = form_textarea($data,$default,$this->js);
			
		}
		else if ($type == 'submit') 
		{
			if (!$this->label) $this->label = 'Envoyer';
			$todo = ''; $bef = '';
			
			if ($this->js) 
		{
				$bef = "var reponse = prompt('".$this->size2."','');";
				$test = "reponse != false";
				$todo = "this.form.".$this->js.".value = reponse;";
			}
			else if ($this->size2) $test = "confirm('".$this->size2."')";
			else $test = true;
			
			$js = 'onClick="'.$bef.' if('.$test.') {'.$todo.'this.form.elements[0].value = \''.$this->size_or_val.'\'; this.form.submit();} return false;"';
			
			$field = form_button($this->name,$this->label,$js);
		}
		else if ($type == 'submitIMG') 
		{
			if (!$this->label) $this->label = 'Envoyer';
			$todo = ''; $bef = '';
			
			if ($this->js) 
			{
				$bef = "var reponse = prompt('".$this->size2."','');";
				$test = "reponse != false";
				$todo = "this.form.".$this->js.".value = reponse;";
			}
			else if ($this->size2) $test = "confirm('".$this->size2."')";
			else $test = true;
			
			
			$js = 'onClick="'.$bef.' if('.$test.') {'.$todo.'this.form.elements[0].value = \''.$this->size_or_val.'\'; this.form.submit();} return false;"';
			
			$field = form_button($this->name,'<img src="'.base_url().'/images/icons/selection/'.$this->label.'.png" />',$js);
		}
		
		$this->field = form_error($this->name,'<div style="padding:0;margin:0;color:red";>','</div>').$field;
	}
	
	function add_rule($rule,$force=false) //regle de verification
	{
		if ($this->rules == '') $this->rules = $rule;
		else 
		{
			$rul = explode('|',$rule);
			$for = explode('|',$this->rules);
			foreach ($rul as $r) if ((!in_array($r,$for))||($force)) $this->rules .= '|'.$r;
		}
		
		return $this;
	}
	
	function rem_rule($rule)
	{
		if($this->rules == $rule) $this->rules = '';
		else 
		{
			str_replace($rule.'|','',$this->rules);
			str_replace('|'.$rule,'',$this->rules);
		}
	}
	
	function add_format($rule,$force=false) //regle de formatage
	{
		if ($this->format == '') $this->format = $rule;
		else 
		{
			$rul = explode('|',$rule);
			$for = explode('|',$this->format);
			foreach ($rul as $r) if ((!in_array($r,$for))||($force)) $this->format .= '|'.$r;
		}
	}
	
	function format($val) //prepare to display
	{
		$rules = explode('|',$this->format);
		
		foreach($rules as $rul) if ($rul != '') $val = $this->{'f_'.$rul}($val,'f');
		
		return $val;
	}
	
	function unformat($val) //prepare to apply rule and record
	{
		$rules = explode('|',$this->format);
		
		foreach($rules as $rul) if ($rul != '') $val = $this->{'f_'.$rul}($val,'uf');
		
		return $val;
	}

	
	//FORMATTING RULES (A DEPLACER AILLEURS)
	function f_flag($val,$way='f')
	{
		if($way == 'f') 
		{
			if($this->show == 'display')
			{
				if ($val == 1) return '<img src="'.base_url().'images/icons/selection/flag_red.png" />';
				elseif ($val == 2) return '<img src="'.base_url().'images/icons/selection/flag_orange.png" />';
				elseif ($val == 3) return '<img src="'.base_url().'images/icons/selection/flag_green.png" />';
				else return '<img src="'.base_url().'images/icons/selection/flag_blue.png" />';
			}
			
		}
			
		else if($way == 'uf');
		
		return $val;
	}
	
	//FORMATTING RULES (A DEPLACER AILLEURS)
	function f_check($val,$way='f')
	{
		if($way == 'f') 
		{
			if($this->show == 'display')
			{
				if ($val == 1) return '<img src="'.base_url().'/images/icons/selection/valid.png" />';
				else return '<img src="'.base_url().'/images/icons/selection/invalid.png" />';
			}
			else if($this->show == 'print')
			{
				if ($val == 1) return '<img src="'.base_url().'/images/icons/selection/valid.png" width="3.5mm" height="auto" />';
				else return '<img src="'.base_url().'/images/icons/selection/invalid.png" width="3.5mm" height="auto" />';
			}
			else if($this->show == 'form')
			{
				if ($val == 1) return true;
				else return false;
			}
		}
			
		else if($way == 'uf');
		
		return $val;
	}
	
	function f_check1($val,$way='f')
	{
		if($way == 'f') 
		{
			if($this->show == 'display')
			{
				if ($val == 1) return '<img src="'.base_url().'/images/icons/selection/valid.png" />';
				else return '';
			}
			else if($this->show == 'print')
			{
				if ($val == 1) return '<img src="'.base_url().'/images/icons/selection/valid.png" width="3.5mm" height="auto" />';
				else return '';
			}
			else if($this->show == 'form')
			{
				if ($val == 1) return true;
				else return false;
			}
		}
			
		else if($way == 'uf');
		
		return $val;
	}
	
	function f_date($val,$way='f')
	{
		if ($val != '')
		{
			if($way == 'f') 
			{	
				if (strtotime($val) > 0) $val = date('d/m/Y',strtotime($val));
			}	
			else if($way == 'uf') 
			{
				list($d,$m,$y) = explode('/',$val);
				if (strtotime($y.'-'.$m.'-'.$d) > 0) $val = date('Y-m-d',strtotime($y.'-'.$m.'-'.$d));
			}
		}
			
		return $val;
	}
	
	function f_nbr($val,$way='f')
	{
		if($way == 'f') 
			if($this->show == 'display') $val = str_replace("\n",'<br />',$val);
			
		return $val;
	}
	
	function f_idlist($val,$way='f')
	{
		if((($this->show == 'display')||($this->show == 'print'))&&($way == 'f')) 
		{
			if ($this->size_or_val) $nt = $this->size_or_val;
			else $nt = $this->name;
			
			list ($g,$table) = explode('id',$nt);
			
			if (($table != '')&&(is_numeric($val)))
			{
				$obj =& get_instance();
				
				$obj->db->select('Nom');
				$obj->db->where($nt, $val);
				$obj->db->limit(1);
				$query = $obj->db->get($table);
				
				if ($query->num_rows() > 0)
				{
					$row = $query->row();
					return $row->Nom;
				}
			}
		}	
		
		return $val;
	}
	
	function f_mlist_text($val,$way='f')
	{
		if ((($this->show == 'display')||($this->show == 'print'))&&($way = 'f')) $val = $this->f_array($val,'f'); 
		else if ($way = 'uf') $val = $this->f_array($val,'uf');

		return $val;
	}
	
	function f_mlist_id($val,$way='f')
	{
		if((($this->show == 'display')||($this->show == 'print'))&&($way = 'f')) 
		{
			$table = $this->name;			
			if ($table != '')
			{
				$ret = array();
				$val = explode('|',$val);
				$obj =& get_instance();
				
				foreach($val as $v)
				{
					$obj->db->select('Nom');
					$obj->db->where('id'.$this->name, $v);
					$obj->db->limit(1);
					$query = $obj->db->get($table);
					
					if ($query->num_rows() > 0)
					{
						$row = $query->row();
						$ret[] = $row->Nom;
					}
				}
				
				$val = $this->f_array($ret,'f');
			}
		}	
		else if ($way = 'uf') $val = $this->f_array($val,'uf');
	
		
		return $val;
	}
	
	function f_array($val,$way='f')
	{
		if($way == 'f')
		{
			$ret = '';
			if(!is_array($val)) $val = explode('|',$val); 
			foreach($val as $v) 
			{
				if ($ret!='') $ret .= ', ';
				$ret .= $v;
			}
			$val = $ret;
		}
		else if (($way == 'uf')&&(is_array($val)))
		{
			$ret = '';
			foreach($val as $key=>$v)
			{
				if (trim($v) != '')
				{
					if ($ret!='') $ret.= '|';
					$ret.=$v;
				}
			}
			$val = $ret;
		}
		
		return $val;
	}
}
