<?php 
   class Authentication_model extends CI_Model {
	
   function __construct() { 
         parent::__construct(); 
		
   } 
   
   public function get($data){
	   $this->db->where('email',$data['email']);
	   $query = $this->db->get('user_tbl');
	   if($query->num_rows() > 0){
			$result = $query->row_array();
	   }else{
			$result = 0;
	   }
	   return $result;
   }
   
   public function add($data){
	   if($this->db->insert('user_tbl',$data)){
			return true;
	   }else{
		   return false;
	   }
   }
   
}


