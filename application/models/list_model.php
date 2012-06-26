<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class List_model extends Model
{
	private $table;
	private $liste;
	
	
	function __construct()
	{
		parent::Model();
	}
	
	function get($tbl='',$where=null,$full=false,$order=false)
	{
		$this->liste = array();
		$this->table = $tbl;

		if ($this->table != '')
		{
			if (!$full) $this->db->select('id'.$this->table.',Nom');
			
			if ($where != null)  $this->db->where($where);
			if ($order) $this->db->order_by($order);
			
			if ($full) $fields = $this->db->list_fields($this->table);
			
			$results = $this->db->get($this->table)->result();
			
			foreach ($results as $res) 
			{
				if ($full)
				{
					$this->liste[$res->{'id'.$this->table}] = $res;
					
					//id translation
					foreach($fields as $field)
					{
						$t = explode('id',$field);
						
						if (($t[0] == '')&&($t[1] != '')&&(($full === true) or (in_array($t[1],$full))))  //Si le champ est de la forme idTruc, on recupere le nom correspondant dans la table truc associée
						{
							$Ttable = $t[1];
							$this->db->select('Nom');
							$this->db->where(array($field => $res->{$field}));
							$this->db->limit(1);
							$query = $this->db->get($Ttable);
							
							if ($query->num_rows() > 0)
								$this->liste[$res->{'id'.$this->table}]->{$Ttable} = $query->row()->Nom;
						}
					}
				}
				else $this->liste[$res->{'id'.$this->table}] = $res->Nom;
			}
		}
		
		return $this->liste;
	}
}
