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
}
