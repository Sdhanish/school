<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'models/Division_model.php';

/**
 * Section_model (Backward-compatibility bridge for Division_model)
 * Extends Division_model to maintain seamless compatibility.
 */
class Section_model extends Division_model {
    public function __construct()
    {
        parent::__construct();
    }
}
