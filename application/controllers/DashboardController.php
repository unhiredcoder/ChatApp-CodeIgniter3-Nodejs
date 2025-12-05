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
            redirect('AuthController','refresh');
            return;
        }
        // Decode JWT payload
        $payload = decode_jwt($jwt);
        if (!$payload || !isset($payload['id'])) {
            echo "Invalid token!";
            return;
        }

        $userId = $payload['id'];
        // Call backend API to get fresh profile
        $url = "http://10.10.15.140:7360/api/user/" . $userId;
        $nameresp = $this->curl_library->simple_get($url);
        $user = json_decode($nameresp, true);
        $username = $user['username'];

        $data['userId'] = $userId;
        $data['username'] = $username;
        $this->load->view("Dashboard", $data);
    }
}
?>