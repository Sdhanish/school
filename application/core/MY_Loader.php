<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Loader
 *
 * Extends CI_Loader to automatically enforce Asia/Kolkata (+05:30)
 * session timezone whenever the database connection is loaded.
 */
class MY_Loader extends CI_Loader {

    public function database($params = '', $return = FALSE, $query_builder = NULL)
    {
        $res = parent::database($params, $return, $query_builder);
        if ($return === TRUE) {
            if (is_object($res) && !empty($res->conn_id)) {
                $res->query("SET time_zone = '+05:30'");
            }
            return $res;
        }

        $CI =& get_instance();
        if (isset($CI->db) && is_object($CI->db) && !empty($CI->db->conn_id)) {
            $CI->db->query("SET time_zone = '+05:30'");
        }
        return $res;
    }
}
