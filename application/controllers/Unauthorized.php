<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Unauthorized extends MY_Controller {

    public function index()
    {
        $this->render('pages/unauthorized', array(
            'title'    => 'Access Restricted',
            'page_key' => 'unauthorized',
        ));
    }
}
