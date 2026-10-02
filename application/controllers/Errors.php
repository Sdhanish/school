<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function error_404()
    {
        $this->output->set_status_header(404);
        
        $data['title'] = '404 - Page Not Found';
        $data['heading'] = '404 - Page Not Found';
        $data['message'] = 'The requested URL was not found on this server. Please check the address or return to the dashboard.';
        
        $this->load->view('pages/errors/404', $data);
    }
}
