<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('Welcome_model');
	} 
	
	public function index()
	{
		$output['message'] = 'this is data from controller';
		$this->load->view('welcome_message',$output);
	}
	
	public function add()
	{
		$this->form_validation->set_rules('title','Title','required');
		if($this->form_validation->run() == true)
		{
			$data = array(
			'title'			=>	$this->input->post('title'),
			);
			
			if($this->Welcome_model->add($data) == true){
			$response 	= array('status'=>'success','message'=>'Project Create Successfully'); 
			} 
		}else{
			$output 	= array('title'=>form_error('title'));
			$response 	= array('status'=>'error','message'=>$output,);
		}
		echo json_encode($response);
	}
	
	public function get(){
		
		$data = array(
			'search'		=>'',
			'items_per_page'=>0,
			'start'			=>0,
			'order_by'		=>'title',
			'order_type'	=>'DESC',
		);
		if(isset($_POST['search'])){$data['search'] = $_POST['search'];}
		if(isset($_POST['items_per_page'])){$data['items_per_page'] = $_POST['items_per_page'];}
		if(isset($_POST['current_page'])){$data['start'] = ($_POST['current_page']-1)*$data['items_per_page'];}
		if(isset($_POST['order_by'])){$data['order_by'] = $_POST['order_by'];}
		if(isset($_POST['order_type'])){$data['order_type'] = $_POST['order_type'];}
		
		$response['message'] = $this->Welcome_model->get($data);
		$response['status']  = 'success'; 
		
		echo json_encode($response); 
	}
	
	public function delete()
	{
		$this->form_validation->set_rules('project_id','Project Id','required');
		if($this->form_validation->run() == true)
		{
			$data = array(
			'id'			=>	$this->input->post('project_id'),
			);
			
			if($this->Welcome_model->delete($data) == true){
			$response 	= array('status'=>'success','message'=>'Project Delete Successfully'); 
			} 
		}else{
			$output 	= array('project_id'=>form_error('project_id'));
			$response 	= array('status'=>'error','message'=>$output,);
		}
		echo json_encode($response);
	}
	
	public function update()
	{
		$this->form_validation->set_rules('title','Title','required');
		$this->form_validation->set_rules('project_id','Project Id','required');
		if($this->form_validation->run() == true)
		{
			$data = array(
			'title'			=>	$this->input->post('title'),
			'id'			=>	$this->input->post('project_id'),
			);
			
			if($this->Welcome_model->update($data) == true){
			$response 	= array('status'=>'success','message'=>'Project Update Successfully'); 
			} 
		}else{
			$output 	= array(
				'title'=>form_error('title'),
				'project_id'=>form_error('project_id')
			);
			$response 	= array('status'=>'error','message'=>$output,);
		}
		echo json_encode($response);
	}
}
