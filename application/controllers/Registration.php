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
            //Is form verified?
            if ($this->input->post('verified')) {
                //Is user verified?
                $results = $this->user->is_valid_account(array('login_name' => trim($this->input->post('loginusername')), 'login_password' => trim($this->input->post('loginpassword'))));
                if ($results['boolean']) {
                    // $this->load->model('_permission');
                    //If so, assign permissions and redirect to their respective dashboard
                    // $this->native_session->set('__permissions', $this->_permission->get_user_permission_list($results['user_id']));
                    //Log sign-in event
                    // $this->_logger->add_event(array('log_code' => 'user_login', 'result' => 'success', 'details' => 'userid='.$results['user_id'].'|username='.trim($this->input->post('loginusername'))));

                    $this->session->sess_expiration = 900; // 15 mins
                    $this->session->sess_expire_on_close = false;

                // Go to the user dashboard
                    // redirect(base_url().'message/inbox');
                }
                // Invalid credentials
                else {
                    $this->_logger->add_event(array('log_code' => 'user_login', 'result' => 'fail', 'details' => 'username='.trim($this->input->post('loginusername'))));
                    $data['msg'] = 'WARNING: Invalid login details.';
                    // $this->load->view('account/login', $data);
                    echo $data;
                }
            } else {
                $data['msg'] = 'ERROR: Your submission could not be verified.';

                // $this->load->view('account/login', $data);
                echo $data;
            }
        }
        // If already logged in, log out of current session
        elseif ($this->native_session->get('__user_id')) {
            $this->logout($this->native_session->get('__user_id'));
            $data['msg'] = 'You have been logged out.';
            echo $data;
            // $this->load->view('account/login', $data);
        }
    }
}
