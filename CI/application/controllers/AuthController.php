<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AuthController extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this -> load ->helper('url');
	}
	public function index()
	{
		$this->load->view('Login');
	}
	public function register() {

		$this->load->view('Register');

	}
	public function createUser() {

        // Set validation rules
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('cpassword', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() == FALSE) {
            // Validation failed → reload form with errors
            $this->load->view('Register');
        } else {
            // Validation passed → save user
            $url = "http://10.10.15.140:5555/api/register";
	        $data = array(
	            'username' => $this->input->post('uname'),
	            'password' => $this->input->post('password')
	        );

	        // Send JSON to Node backend
            $response = $this->curl_library->simple_post($url, $data);

            echo "<pre>";
            print_r($data);
            echo "</pre>";
            echo $response;
            redirect('AuthController','refresh');
	 	}
	}

		public function loginUser() {


            // Validation passed → save user
            $url = "http://10.10.15.140:5555/api/login";
	        $data = array(
	            'username' => $this->input->post('uname'),
	            'password' => $this->input->post('password')
	        );

	        // Send JSON to Node backend
            $response = $this->curl_library->simple_post($url, $data);

            echo "<pre>";
            print_r($data);
            echo "</pre>";
            $res = json_decode($response);
            if($res->success) {
            	echo "Login Successful!";
            }
            else {
            	echo "Login Failed!";
            }
            exit;
	}
	
}
/* End of file AuthController.php */
/* Location: ./application/controllers/AuthController.php */
?>