<?php

// Exception Classes
class NoteUnavailableException extends Exception { }
class HasNoMoneyException extends Exception { }

// Main Class
class Cashier {
	private $arrBills 		= array(100,50,20,10);
	private $intClientTotal = null;
	private $intBillsTotal 	= array(null,null,null,null);
	
	/**
	 * Class constructor
	 * 
	 * @param integer $intClientTotal Client's total money available
	 */
	public function __construct($intClientTotal = null) {
		$this->setClientTotal($intClientTotal);
	}
	
	/**
	 * If client has money limit, sets it
	 * 
	 * @param integer $intTotal Client's total money available
	 */
	public function setClientTotal($intTotal) {
		$this->intClientTotal = $intTotal;
	}
	
	/**
	 * Cashier method
	 * 
	 * @param integer $intTotal Amount required
	 * @param integer $returnMode	If 1, returns simple array with given notes; if 0, returns array as (note_value => note_quantity)
	 *  
	 * @throws InvalidArgumentException
	 * @throws HasNoMoneyException
	 * @throws NoteUnavailableException
	 * 
	 * @return Array
	 */
	public function getCash($intTotal = null,$returnMode = 1) {
		try {

			// Checks valid total requested
			if( !is_null($intTotal) && ( $intTotal < 0 || !is_numeric($intTotal) ) )
				throw new InvalidArgumentException('Invalid Argument!');

			// Checks valid return mode
			if( $returnMode != 1 && $returnMode != 0 )
				throw new InvalidArgumentException('Invalid Argument!');
			
			// Checks client total
			if(!is_null($this->intClientTotal) && $this->intClientTotal < $intTotal)
				throw new HasNoMoneyException('Money is not enough!');
			
			// Checks bills availability
			if( ($intTotal % 10) != 0 ) 
				throw new NoteUnavailableException('Note Unavailable!');
			
			// Calcs bills
			$arrReturn = array();
			$intValue = $intTotal;
			foreach($this->arrBills AS $intKey => $intVal) {
				// Checs specific bill availability
				if( $intTotal > $intVal && (is_null($this->intBillsTotal[$intKey]) || $this->intBillsTotal[$intKey] > 0) ) {
					$intModule = $intValue - ($intValue % $intVal);
					$arrReturn[$intVal] = $intModule / $intVal;
	
					$intValue -= $intModule;
				}
			}
			
			return ($returnMode ? array_keys($arrReturn) : $arrReturn);
			
		} catch(Exception $e) {
			print_r($e->getMessage());
		}
	}
}

/**
 * EXAMPLE
 */
echo '<pre>';
$obj = new Cashier();
print_r( $obj->getCash(-130) );
echo '</pre>';
?>
