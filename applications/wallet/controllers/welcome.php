<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller {

    function __construct() {
        parent::__construct();
    }

	public function index()
	{
        // parent controller method that should be called on restricted pages
        // inside redirects to front page
        $this->data['message'] = $this->session->flashdata('message');
        if (!$this->is_authenticated()) {
            return;
        }

        $data['title'] = 'Welcome';
		$this->load->view('welcome', $data);
	}

}
