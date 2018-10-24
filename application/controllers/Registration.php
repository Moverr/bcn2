<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Registration extends CI_Controller
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     *
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        //load ci controller
        parent::__construct();
        $this->load->model('user', 'user');
        //load Models
    }

    public function index()
    {
        // $this->load->model('_validator');
        // $this->load->model('_billingmanagement');
    }

    public function save()
    {
        try {
            $data = filter_forwarded_data($this);

            if (!empty($_POST)) {
                $data['username'] = $_POST['username'];
                $data['emailAddress'] = $_POST['emailAddress'];
                $data['password'] = $_POST['password'];
                $response = $this->user->add($data);
                $response['type'] = 'success';
                $response['message'] = 'Record Saved Successfully';
                echo   $response['message'];
            }
        } catch (Exception $e) {
            $response['type'] = 'error';
            $response['message'] = $e->getMessage();
            echo   $response['message'];
        }
    }

    public function login()
    {
        $data = filter_forwarded_data($this);

        if (!empty($_POST)) {
            //Is user verified?
            $results = $this->user->is_valid_account(array('login_name' => trim($this->input->post('username')), 'login_password' => trim($this->input->post('passwword'))));
            if ($results['boolean']) {
                $userdetails['userid'] = $results[0]['userid'];
                $userdetails['username'] = $results[0]['username'];
                $userdetails['isadmin'] = (!empty($results[0]['groupid']) && $results[0]['groupid'] == 14 ? 'Y' : 'N');

                $userdetails['emailaddress'] = $results[0]['emailaddress'];
                // $userdetails['names'] = $results[0]['firstname'].' '.$results[0]['lastname'];
                // $userdetails['firstname'] = $results[0]['firstname'];
                // $userdetails['lastname'] = $results[0]['lastname'];

                //print_r($userdetails);

                $this->session->set_userdata($userdetails);
                $this->session->set_userdata('alluserdata', $userdetails);
                setcookie('loggedin', 'true', time() + $this->config->item('sess_time_to_update'));

                $data['status'] = 'SUCCESS ';

                var_dump($data);
            // Go to the user dashboard
                // redirect(base_url().'message/inbox');
            }
            // Invalid credentials
            else {
                // $this->_logger->add_event(array('log_code' => 'user_login', 'result' => 'fail', 'details' => 'username='.trim($this->input->post('loginusername'))));
                $data['status'] = 'FAILURE ';
                var_dump($data);
            }
        } else {
            $data['msg'] = 'ERROR: Your submission could not be verified.';
            $data['status'] = 'FAILURE ';

            var_dump($data);
        }
    }
}

// Hope this email finds you well, I want to share the domain name where our project is going to be hosted, https://coincashgroup.com/ .
// | UserName: coincashgroup
// | PassWord: =$-EgeqJd7tR
