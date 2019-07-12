<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Authentication_controller extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('front_end/authentication_model');
		$this->load->library('session');
		define('CLIENT_ID', '915754452074-jnrvm2oga1lkdod1s32sam0qkh9kvd4c.apps.googleusercontent.com');
		define('CLIENT_SECRET', '5GI1E_yuq4fJsxlzJEONvY9y');
		define('CLIENT_REDIRECT_URL', 'http://localhost/myservice/google_login');
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
				$send['to'] 		= $return_data['email'];
				$send['subject'] 	= 'Activat Your Email! ';
				$send['message'] 	= '<body><h1>Email Verification</h1><p>Dear '.$return_data['name'].',</p><p>Thanks for register your account with us. please <strong><a href="'.base_url('activate-email/'.md5($return_data['email'])).'">Click here</a></strong> to verify your account.<br></p><p>Thanks<br><b>Regards Team</b></p></body>';
				//$this->sendMail($send);
				
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
	
	public function google_login(){
		 if(isset($_GET['code'])){
			 $token = $this->GetAccessToken($_GET['code']); 
			if($token['access_token']){
				$url = 'https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,gender,id,picture,verified_email';	
	
				$ch = curl_init();		
				curl_setopt($ch, CURLOPT_URL, $url);		
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $token['access_token']));
				$data = json_decode(curl_exec($ch), true);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);		
				if($http_code != 200) {
					throw new Exception('Error : Failed to get user information');
				}else{
					$this->updateApiEmail($data); 
				}
				
			}
		 }else{
			 $this->google_login = 'https://accounts.google.com/o/oauth2/v2/auth?scope=' . urlencode('https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email') . '&redirect_uri=' . urlencode(CLIENT_REDIRECT_URL) . '&response_type=code&client_id=' . CLIENT_ID . '&access_type=online';
			 redirect($this->google_login); 
		 }
	}
	//get google AccessToken only
	function GetAccessToken($code) {	
		$url = 'https://www.googleapis.com/oauth2/v4/token';			

		$curlPost = 'client_id=' . CLIENT_ID. '&redirect_uri=' . CLIENT_REDIRECT_URL . '&client_secret=' . CLIENT_SECRET . '&code='. $code . '&grant_type=authorization_code';
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
		$data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
		if($http_code != 200) 
			throw new Exception('Error : Failed to receieve access token');
		
		return $data;
	}
	
	public function facebook_login(){
		$this->load->library('facebook');
		$userData = array();
		if($this->facebook->is_authenticated()){	
			$user_data = $this->facebook->request('get', '/me?fields=id,first_name,last_name,email,gender,locale,picture');
			$data = array(
				'email'=>$user_data['email'],
				'name'=>$user_data['first_name'].' '.$user_data['last_name']
			); 
			$this->updateApiEmail($data);
		}
		else
		{
            $data['authUrl'] =  $this->facebook->login_url();
			redirect($data['authUrl']);
        }
	}
	
	public function updateApiEmail($data){
		$send = array(
			'email' => $data['email']
		); 
				
		$return_data = $this->authentication_model->get($send);
		if($return_data){
			$userData = array(
				'email'=>$return_data['email'],
				'name'=>$return_data['name'],
				'user_id'=>$return_data['user_id']
			); 
			$this->session->set_userdata('myservice_user',$userData); 
		}else{
			$send = array(
				'email'=> $data['email'],
				'password'=>'',
				'name'=>$data['name']
			);
					
			if($this->authentication_model->add($send)){
				$userData = array(
					'email'=>$return_data['email'],
					'name'=>$return_data['name'],
					'user_id'=>$return_data['user_id']
				); 
				$this->session->set_userdata('myservice_user',$userData);
			} 
					
		}
				
		if($this->session->userdata('myservice_user')){
			redirect(base_url().'profile');
		}else{
			redirect(base_url().'login');
		}
	}
	
	public function forget_password(){
		$output['error'] = array('email'=>'');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		if($this->form_validation->run() === TRUE){
			$email = $this->security->xss_clean($this->input->post('email'));
			
			$data = array(
				'email' => $this->input->post('email')
			); 
			
			$return_data = $this->authentication_model->get($data);
			if($return_data){
				$send['to'] 		= $return_data['email'];
				$send['subject'] 	= 'App Demo - Forgot Password ! ';
				$send['message'] 	= '<body><h1>Email Verification</h1><p>Dear '.$return_data['name'].',</p><p>Thanks for register your account with us. please <strong><a href="'.base_url('verify-code/'.md5($return_data['email'])).'">Click here</a></strong> to verify your account.<br></p><p>Thanks<br><b>Regards Team</b></p></body>';
				//$this->sendMail($send);
				$created_date = time()+3600;
				$encCode  = base64_encode($email).'____'.md5($created_date);
				echo base_url('verify-code/'.$encCode); die;
				redirect(base_url().'login'); 
			}else{
				$output['error']['email'] = 'Email address not found'; 
			}
		}else{
			if($this->session->userdata('myservice_user')){
				redirect(base_url().'profile');
			}else{
				$this->load->view($this->config->item('frontend_folder').'header');
				$this->load->view($this->config->item('frontend_folder').'forget_password',$output);
				$this->load->view($this->config->item('frontend_folder').'footer');
			}
		}
	}
	
	public function reset_password(){
		$output['error'] = array('email'=>'');
		if($_POST){
			
		
			$this->form_validation->set_rules('password', 'password', 'trim|required|md5');
			$this->form_validation->set_rules('code', 'code', 'required');
			if($this->form_validation->run() === TRUE){
				$code = $this->input->post('code');
				$password = $this->security->xss_clean($this->input->post('password'));
				$cpassword = $this->security->xss_clean($this->input->post('cpassword'));
				if($password == md5($cpassword)){
					$ex_code = explode('____',$code);		
					$decEmail = base64_decode($ex_code[0]);
					$data = array(
						'email' => $decEmail,
						'password'=>$this->input->post('password')
					); 
					$return_data = $this->authentication_model->update($data);
					if($return_data){
						redirect(base_url().'login'); 
					}else{
						$output['error']['email'] = 'Email address not found'; 
					}
				}
			}
		}
		if($this->session->userdata('myservice_user')){
			redirect(base_url().'profile');
		}else{
			$this->load->view($this->config->item('frontend_folder').'header');
			$this->load->view($this->config->item('frontend_folder').'reset_password',$output);
			$this->load->view($this->config->item('frontend_folder').'footer');
		}
	}
	
	public function verify_code($code){
		$output['error'] = array('email'=>'');
		if($code != ''){
			$ex_code = explode('____',$code);		
			$decEmail = base64_decode($ex_code[0]);
			
			$data = array('email' => $decEmail); 
			$return_data = $this->authentication_model->get($data);
			
			if($return_data){
				redirect(base_url().'reset-password?code='.$code); 
			}else{
				$output['error']['email'] = 'Verification Code Expire'; 
				$this->load->view($this->config->item('frontend_folder').'header');
				$this->load->view($this->config->item('frontend_folder').'forget_password',$output);
				$this->load->view($this->config->item('frontend_folder').'footer');
			}
		}else{
			redirect(base_url().'login'); 
		}
	}
	
	public function sendMail($data){
		$this->load->library('email');
		
		$from = array(
			'email' => 'rajanamdav@gmail.com', 
			'name' => 'APP DEMO',
			'user' => $this->config->item('smtp_user')
		);
		
        $reply_to = $from;
		
		$this->email->set_newline("\r\n");
        $this->email->from($from['email'], $from['name'], $from['user']);
		$this->email->reply_to($reply_to['email'],$reply_to['name']);
        $this->email->to($data['to']);
        $this->email->subject($data['subject']);
        $this->email->message($data['message']);
		$this->email->set_mailtype("html");		
		
        if ($this->email->send()) {
            return true;
        } else {
            show_error($this->email->print_debugger());
        }
	}
}
