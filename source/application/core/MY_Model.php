<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {
	
	public function __construct() {
        parent::__construct();
    }
	
	public function lastId() {
		return $this->db->insert_id();
	}
	
}

?>
