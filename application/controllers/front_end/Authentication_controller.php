<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authentication_controller extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('front_end/authentication_model');
		$this->load->library('session');
	} 
	
	public function index(){
		//$this->load->view($this->config->item('frontend_folder').'login_page');
	}
	
	public function login(){
		$output['error'] = array('email'=>'','password'=>'');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('password', 'password', 'trim|required|md5|min_length[8]');
		if($this->form_validation->run() === TRUE){
			$email = $this->security->xss_clean($this->input->post('email'));
			$password = $this->security->xss_clean($this->input->post('password'));
			
			$data = array(
				'email' => $this->input->post('email')
			); 
			
			$return_data = $this->authentication_model->get($data);
			if($return_data){
				if($return_data['password'] === $password){
					$userData = array(
						'email'=>$return_data['email'],
						'name'=>$return_data['name'],
						'user_id'=>$return_data['user_id']
					); 
					$this->session->set_userdata('myservice_user',$userData); 
				}else{
					$output['error']['password'] = 'Password not match'; 
				}
			}else{
				$output['error']['email'] = 'Email address not found'; 
			}
		}
		if($this->session->userdata('myservice_user')){
			redirect(base_url().'profile');
		}else{
			$this->load->view($this->config->item('frontend_folder').'header');
			$this->load->view($this->config->item('frontend_folder').'login_page',$output);
			$this->load->view($this->config->item('frontend_folder').'footer');
		}
	}
	
	public function signup(){
		$output['error'] = array('email'=>'','password'=>'','name'=>'');
		$this->form_validation->set_rules('email','Email','trim|required|valid_email|is_unique[user_tbl.email]');
		$this->form_validation->set_rules('password','Password','trim|required|md5|min_length[8]');
		$this->form_validation->set_rules('name','name','trim|required');
		if($this->form_validation->run() === TRUE){
			$email = $this->security->xss_clean($this->input->post('email')); 
			$password = $this->security->xss_clean($this->input->post('password')); 
			$name = $this->security->xss_clean($this->input->post('name')); 
			
			$data = array(
				'email'=> $email,
				'password'=>$password,
				'name'=>$name
			);
			
			if($this->authentication_model->add($data)){
				redirect(base_url().'login'); 
			}else{
				$output['error']['message'] = 'Error!, Please try again'; 
			}; 
		}
		if($this->session->userdata('myservice_user')){
			redirect(base_url().'profile');
		}else{
			$this->load->view($this->config->item('frontend_folder').'header');
			$this->load->view($this->config->item('frontend_folder').'signup_page',$output);
			$this->load->view($this->config->item('frontend_folder').'footer');
		}
	}
	
	public function profile(){
		if($this->session->userdata('myservice_user')){
			$data = $this->session->userdata('myservice_user'); 
			$this->load->view($this->config->item('frontend_folder').'header');
			$this->load->view($this->config->item('frontend_folder').'profile_page',$data);
			$this->load->view($this->config->item('frontend_folder').'footer');
		}else{
			redirect(base_url().'login'); 
		}
	}
	
	public function logout(){
		
		$this->session->unset_userdata('myservice_user');
		$this->session->sess_destroy();
		
		redirect(base_url().'login'); 
		
	}
}
