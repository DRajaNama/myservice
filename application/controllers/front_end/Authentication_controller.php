<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authentication_controller extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('front_end/Login_model');
	} 
	
	public function index()
	{
		//$this->load->view($this->config->item('frontend_folder').'login_page');
	}
	
	public function login()
	{
		$output['data'] = '';
		// $this->form_validation->set_rules('email', 'Email', 'required|is_unique[users.email]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'password', 'trim|required|md5');
		if($this->form_validation->run() === TRUE){
			$email = $this->security->xss_clean($this->input->post('email'));
			$password = $this->security->xss_clean($this->input->post('password'));
			
			$data = array(
				'email' => $this->input->post('email')
			); 
			
			
		}
		
		$this->load->view($this->config->item('frontend_folder').'login_page',$output);
	}
	
	public function signup()
	{
		$this->load->view($this->config->item('frontend_folder').'signup_page');
	}
	
	
}
