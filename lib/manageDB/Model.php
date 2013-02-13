<?php
class manageDB_Model extends Model {
	
	
	public function __construct() {
		parent::__construct();
		
		if($this->boolConnStatus) {
			$this->objConn->loadModule('Manager');
		}
	}
}
?>