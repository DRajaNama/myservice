<?php 
   class Welcome_model extends CI_Model {
	
   function __construct() { 
         parent::__construct(); 
		
   } 
   public function add($data){
	   $this->db->insert('tbl_project',$data);
	  return $this->db->insert_id();
   }
   public function update($data){
	   $this->db->where('id',$data['id']);
       $this->db->update('tbl_project',$data);
       return true;
   }
   public function delete($data){
       $this->db->where('id',$data['id']);
       $this->db->delete('tbl_project');
       return true;
   }
   public function get($data){
		
		$this->db->select('*');
		$this->db->like('title',$data['search']);
        $query = $this->db->get('tbl_project');
		$datas['total_record'] = $query->num_rows();
		
		$this->db->select('*');
		$this->db->like('title',$data['search']);
        $this->db->order_by($data['order_by'],$data['order_type']);
        $this->db->limit($data['items_per_page'],$data['start']);
        $query = $this->db->get('tbl_project');
		$datas['records'] = $query->result();
		
		return $datas;
   }
   public function projects($data){
	  
		$this->db->select($data['fields']);
        $this->db->group_start();
		$this->db->or_like('created_date',$data['search']);
        $this->db->or_like('title',$data['search']);
        $this->db->group_end();
		$this->db->where('status != ',0,FALSE);
		if($data['start_date'] !='' && $data['end_date'] != ''){
			$this->db->where('created_date > ',$data['start_date'],FALSE);
			$this->db->where('created_date  < ',$data['end_date'],FALSE);
		}
		$query = $this->db->get("page_projects");
        //echo $this->db->last_query(); die;
        $datas['total_record'] = $query->num_rows();
		
		$this->db->select($data['fields']);
		$this->db->group_start();
		$this->db->like('title',$data['search']);
        $this->db->or_like('created_date',$data['search']);
		$this->db->group_end();
		$this->db->where('status != ',0,FALSE);
		if($data['start_date'] !='' && $data['end_date'] != ''){
			$this->db->where('created_date > ',$data['start_date'],FALSE);
			$this->db->where('created_date  < ',$data['end_date'],FALSE);
		}
        $this->db->order_by($data['order_by'],$data['order_type']);
        $this->db->limit($data['items_per_page'],$data['start']);
        $query = $this->db->get('page_projects');
        $datas['records'] = $query->result();
        return $datas;
   }
   
}


