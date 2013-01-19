<?php
include 'Mail.php';

class PEAR_ApplrSendMail extends Mail {
	public function __construct() {
		
	}
	
	public function factory($driver, $params = array()) {
		return parent::factory($driver, $params);
	}
}
?>