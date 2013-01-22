<?php
include 'MDB2.php';

class PEAR_ApplrDB extends MDB2 {
	public function __construct() {
		
	}
	
	public function connect($dsn,$options) {
		return parent::connect($dsn,$options);
	}
	
	public function singleton($dsn,$options) {
		return parent::singleton($dsn,$options);
	}
}
?>