<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Caramel extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
		$this->layout->set_theme('layoutCaramel');        
    }
    
}

?>