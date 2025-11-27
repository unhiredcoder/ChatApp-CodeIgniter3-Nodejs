<?php
class DashboardController extends CI_Controller{
		public function __construct()
	{
		parent::__construct();
		$this->load->helper(['url', 'cookie', 'jwt']);

	}
	function index(){
		$this->load->helper('url');

        //  Get JWT from session
        $jwt = get_cookie("jwt_token");
        if (!$jwt) {
            echo "Not logged in!";
            return;
        }
        // Decode JWT payload
        $payload = decode_jwt($jwt);
        if (!$payload || !isset($payload['id'])) {
            echo "Invalid token!";
            return;
        }

        $userId = $payload['id'];
        $username = $payload['username'];

        $data['userId'] = $userId;
        $data['username'] = $username;
        $this->load->view("Dashboard", $data);
	}
}
?>