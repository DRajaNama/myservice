<?php 
   class Authentication_model extends CI_Model {
	
   function __construct() { 
         parent::__construct(); 
		
   } 
   
   public function getRow($data){
	   $this->db->where('email',$data['email']);
	   $query = $this->db->get('user');
	   $result = $query->row();
   }
}


