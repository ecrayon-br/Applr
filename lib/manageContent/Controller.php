<?php
class manageContent_Controller extends Controller {
	public		$intSection;
	public		$objSection;
	public		$objFields;
	public		$objRelFields;
	
	public		$objData;
	public		$arrRelData;
	public		$objPrintData;
	public		$intContent;
	public		$arrRelContent;
	
	protected	$objModel;
	
	/**
	 * Class constructor, sets $this->intSection and instantiates $this->objConn
	 * 
	 * @param	integer	$intSection	Section ID
	 *
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 * @todo	Implement UPLOAD field sufyx
	 * @todo	Implement PUBLICA! field sufyxes
	 * @todo	Define default content fields
	 * @todo	Verify errors
	 *
	 */
	public function __construct($intSection) {
		if(!is_numeric($intSection) || empty($intSection)) return false;
		
		parent::__construct();
		
		if($intSection != SECTION_ID) {
			$this->objSection	= $this->objModel->getSectionConfig($intSection);
		} else {
			$this->objSection = parent::$objSection;
		}
		
		$this->objModel 	= new manageContent_Model();
		
		$this->setSection($intSection);
		$this->getSectionFields();
		$this->getRelSectionFields();
	}
	
	/**
	 * Sets $this->intSection value
	 * 
	 * @param integer $intSection Main section value
	 *
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	public function setSection($intSection) {
		if(!is_numeric($intSection) || empty($intSection)) return false;
		
		$this->intSection = $intSection;
	}
	
	/**
	 * Get SECTION fields data
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getSectionFields() {
		$this->objFields = $this->objModel->getSectionFields($this->intSection);
	}
	
	/**
	 * Get RELATIONSHIP fields data
	 *
	 * @return	MDB2_Data_Object
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getRelSectionFields() {
		$this->objRelFields = $this->objModel->getRelSectionFields($this->intSection);
	}
	
	
	/**
	 * Checks field's sufyx and sets $arrData and $arrPrintData values
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	private function setupFieldSufyx($mxdData) {
		if(!is_array($mxdData)) return false;
		
		foreach($this->objFields AS $mxdKey => $strField) {
			$this->objData->$strField = (is_array($mxdData) ? (!empty($mxdData[$strField]) ? $mxdData[$strField] : '') : (!empty($mxdData->$strField) ? $mxdData->$strField : ''));
			
			$strTemp = end(explode('_',$strField));
			switch($strTemp) {
				case 'date':
					$intDay		= $strField . '_Day';
					$intDay		= (is_array($mxdData) ? (!empty($mxdData[$intDay]) 		? $mxdData[$intDay]		: '00') 	: (!empty($mxdData->$intDay) 	? $mxdData->$intDay		: '00') );
					$intMonth	= $strField . '_Month';
					$intMonth	= (is_array($mxdData) ? (!empty($mxdData[$intMonth])	? $mxdData[$intMonth]	: '00') 	: (!empty($mxdData->$intMonth) 	? $mxdData->$intMonth	: '00') );
					$intYear	= $strField . '_Year';
					$intYear	= (is_array($mxdData) ? (!empty($mxdData[$intYear])		? $mxdData[$intYear]	: '0000') 	: (!empty($mxdData->$intYear) 	? $mxdData->$intYear	: '0000') );
					
					if($intDay > 0 || $intMonth > 0 || $intYear > 0) 	{
						$intDay		= ($intDay	> 0 ? $intDay	: date('d'));
						$intMonth	= ($intMonth> 0 ? $intMonth	: date('m'));
						$intYear	= ($intYear	> 0 ? $intYear	: date('Y'));
						
						$this->objData->$strField 		= $intYear.'-'.$intMonth.'-'.$intDay;
						$this->objPrintData->$strField	= (LANGUAGE != 1 ? date('Y-m-d',strtotime($this->objData->$strField)) : date('d/m/Y',strtotime($this->objData->$strField)));
					} else {
						$this->objData->$strField		= '0000-00-00';
						$this->objPrintData->$strField	= '0000-00-00';
					}
				break;
				
				case 'money':
					switch($this->objData->$strField) {
						default:
						case 1:
							$this->objPrintData->$strField	= 'R$';
						break;
						
						case 2:
							$this->objPrintData->$strField	= 'USD';
						break;
						
						case 3:
							$this->objPrintData->$strField	= 'EUR';
						break;
					}
				break;
				
				case 'phone':
					if(!empty($this->objData->$strField)) 	{
						$intPhone = $strField . '_ddd';
						$intPhone = (is_array($mxdData) ? (!empty($mxdData[$intPhone]) ? $mxdData[$intPhone] : '') : (!empty($mxdData->$intPhone) ? $mxdData->$intPhone : '') );
						
						$this->objPrintData->$strField	= $intPhone . $this->objData->$strField;
						$this->objData->$strField		= Controller::onlyNumbers($intPhone . $this->objData->$strField);
					} else {
						$this->objPrintData->$strField = '';
					}
				break;
				
				case 'upload':
				break;
				
				default:
					$this->objPrintData->$strField = $mxdData;
				break;
			}
		}
	}

	/**
	 * Insert main content and sets $this->contentID as last insert ID value
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function insertMainContent($mxdData) {
		if(!is_array($mxdData)) return false;
		
		$this->setupFieldSufyx($mxdData);
		$this->intContent = $this->objModel->insert($this->objSection->table,(array) $this->objData,true);
	}
	
	/**
	 * Insert relationship contents and sets $this->arrRelContent with related NAME attr
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function insertRelContent($mxdData) {
		if(!is_array($mxdData)) return false;
		
		foreach($this->objRelFields AS $mxdKey => $objRel) {
			// rel_ctn_PARENT_NAME_ctn_CHILD_NAME
			$arrTmpTable	= explode('_ctn_',$objRel->table);
			if($objRel->parent) {
				$strParentTable = 'ctn_' . $arrTmpTable[1];
				$strParentField	= $strParentTable . '_id';
				$strChildTable	= 'ctn_' . $arrTmpTable[2];
				$strChildField	= $strChildTable . '_id';
			} else {
				$strParentTable = 'ctn_' . $arrTmpTable[2];
				$strParentField	= $strParentTable . '_id';
				$strChildTable	= 'ctn_' . $arrTmpTable[1];
				$strChildField	= $strChildTable . '_id';
			}
			
			$this->setupRelField($mxdData,$objRel->field);
			
			foreach($this->arrRelData[$objRel->field] AS $intRelID) {
				if($this->objModel->insert($objRel->table, ($objRel->parent ? array($strParentField => $this->intContent, $strChildField => $intRelID) : array($strParentField => $intRelID, $strChildField => $this->intContent)) )) {
					$this->arrRelContent[$objRel->field][] = $this->recordExists('name', ($objRel->parent ? $strParentTable : $strChildTable),'id = ' . $intRelID,true);
				}
			}
		}
	}
	
	/**
	 * Checks for relationship sent data and sets $this->arrRelData to insert
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * @param	string	$strRelField	DB::rel_sec_sec.field_name value
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	private function setupRelField($mxdData,$strRelField) {
		if(!is_array($mxdData)) return false;
		if(!is_string($strRelField) || empty($strRelField)) return false;
		
		$mxdData = (array) $mxdData;
		
		if(isset($mxdData[$strRelField]) && is_numeric($mxdData[$strRelField])) {
			$this->arrRelData[$strRelField][]	= $mxdData[$strRelField];
		} elseif(isset($mxdData[$strRelField]) && is_array($mxdData[$strRelField])) {
			$this->arrRelData[$strRelField][]	= array_merge($this->objRelData->$strRelField,$mxdData[$strRelField]);
		} else {
			foreach($mxdData AS $strKey => $mxdValue) {
				if(strpos($strKey,$strRelField) === 0) {
					$this->arrRelData[$strRelField][] = end(explode('_',$strKey));
				}
			}
		}
	}

	/**
	 * Inserts SECTION data
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * 
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function insert($mxdData) {
		if(!is_array($mxdData)) return false;
		
		$this->insertMainContent($mxdData);
		$this->insertRelContent($mxdData);
	}
}
?>