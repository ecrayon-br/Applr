<?php
class manageContent_Controller extends CRUD_Controller {
	public		$objSection;
	public		$objField;
	public		$objRelField;
	
	public		$arrRelData		= array();
	public		$objPrintData;
	public		$intContentID;
	public		$arrRelContent	= array();
	
	protected	$intSecID;
	protected	$objRelParams;
	protected	$objStruct;
	
	/**
	 *
	 * ATENTION!
	 *
	 * ALL PROTECTED VARS BELOWS MUST BE SET UP WITH DATABASE AND RESPECTIVE DATA FOR APPLR TO WORK!
	 *
	 */
	/**
	 * @todo set PROJECT_ID
	 */
	protected	$strTable			= 'sec_config';
	protected	$arrTable			= array('sec_config');
	
	protected	$arrFieldList		= array('rel_sec_struct.*','sec_config_order.field_order','sec_config_order.type','sec_struct.name AS sec_struct_name','sec_struct.html','sec_struct.fieldtype','sec_struct.suffix');
	protected	$arrJoinList		= array('JOIN rel_sec_struct','JOIN sec_config_order','JOIN sec_struct');
	protected	$arrWhereList		= array(
											'rel_sec_struct.sec_config_id = sec_config.id',
											'rel_sec_struct.id = sec_config_order.field_id',
											'rel_sec_struct.sec_struct_id = sec_struct.id'
										);
	protected	$arrGroupList		= array('rel_sec_struct.id');
	protected	$arrOrderList		= array('rel_sec_struct.id');
	
	protected	$arrFieldData		= array('rel_sec_struct.*','sec_config_order.field_order','sec_config_order.type','sec_struct.name AS sec_struct_name','sec_struct.fieldtype','sec_struct.suffix');
	protected	$arrJoinData		= array('JOIN rel_sec_struct','JOIN sec_config_order ON sec_config_order.type = 1','JOIN sec_struct');
	protected	$arrWhereData		= array(
											'rel_sec_struct.sec_config_id = sec_config.id',
											'rel_sec_struct.id = sec_config_order.field_id',
											'rel_sec_struct.sec_struct_id = sec_struct.id'
											);
	protected	$arrGroupData		= array('rel_sec_struct.id');
	protected	$arrOrderData		= array('rel_sec_struct.id');
	
	protected	$arrRelFieldData	= array('rel_sec_sec.*','sec_config_order.field_order','sec_config_order.type', 'rel_sec_language.name AS child_name', 'child_config.table_name AS child_section_table_name', 'child_config.permalink AS child_section_permalink', 'child_config.sys_folder_id AS child_sys_folder_id', 'CONCAT(GROUP_CONCAT(DISTINCT rel_sec_struct.field_name SEPARATOR ","),",date_create,date_publish,date_expire,seo_description,seo_keywords,permalink") AS child_fields');
	protected	$arrRelJoinData		= array('JOIN rel_sec_sec','JOIN sec_config_order ON sec_config_order.type = 2', 'JOIN rel_sec_language', 'JOIN sec_config AS child_config', 'JOIN rel_sec_struct ON rel_sec_struct.sec_config_id = child_id');
	protected	$arrRelWhereData	= array(
											'rel_sec_sec.sec_config_id = sec_config.id',
											'rel_sec_sec.id = sec_config_order.field_id',
											'rel_sec_sec.child_id = rel_sec_language.sec_config_id',
											'rel_sec_sec.child_id = child_config.id'
										);
	protected	$arrRelGroupData	= array('rel_sec_sec.id');
	protected	$arrRelOrderData	= array('rel_sec_sec.id');
	
	/**
	 * Class constructor, sets $this->intSecID and instantiates $this->objConn
	 * 
	 * @param	integer	$intSecID	Section ID
	 * @param	string	$strTemplateDir	Sets SMARTY template dir path
	 *
	 * @return	void
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 * @todo	Define default content fields
	 * @todo	Implement PUBLICA! field sufyxes
	 * @todo	Checks insert IMG / VIDEO content
	 * @todo	Sets image::media_gallery_id
	 * @todo	Sets image::filepath_thumbnail
	 * @todo	Sets video::media_gallery_id
	 * @todo	Sets video::filepath_streaming
	 * @todo	Sets $this->configContentLink MEDIA GALLERY link
	 *
	 */
	public function __construct($intSecID = 0,$checkAuth = true,$strTemplateDir = ROOT_TEMPLATE) {
		parent::__construct(false,$checkAuth,$strTemplateDir);
		
		$this->setSection($intSecID);
	}
	
	/**
	 * Sets $this->intSecID value
	 * 
	 * @param integer $intSecID Main section value
	 *
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	public function setSection($intSecID) {
		if(!is_numeric($intSecID) || empty($intSecID)) return false;
		
		if($this->getSectionConfig($intSecID)) {
			$this->intSecID = $intSecID;
			
			$this->getSectionFields();
			$this->getRelSectionFields();
		}
		return true;
	}

	/**
	 * Get SECTION config data and sets $this->objSection
	 *
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getSectionConfig($intSecID = 0) {
		if(!$intSecID) $intSecID = $this->intSecID;
		
		$this->objSection	= $this->objModel->getSectionConfig($intSecID);
		$this->objSmarty->assign('objSection',$this->objSection);
		
		$arrOrder = explode(',',$this->objSection->orderby);
		foreach($arrOrder AS &$strOrder) {
			$strOrder = trim($strOrder);
			
			if(strpos($strOrder,'.') === false) $strOrder = $this->objSection->table_name . '.' . $strOrder;
		}
		$this->objSection->orderby = implode(', ',$arrOrder);
		
		if($this->objSection === false) return false; else return true;
	}
	
	/**
	 * Get SECTION fields data and sets $this->objField
	 *
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getSectionFields() {
		$this->objField = $this->objModel->select('field_name','rel_sec_struct',array(),'sec_config_id = ' . $this->intSecID,array(),array(),0,null,'Col');
		
		if($this->objField === false) return false; else return true;
	}
	
	/**
	 * Get RELATIONSHIP fields data
	 *
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getRelSectionFields($intSecID = 0) {
		if(empty($intSecID)) $intSecID = $this->intSecID;
		
		$arrFields	= $this->arrRelFieldData;
		$arrFields[]= 'IF(rel_sec_sec.sec_config_id = ' . $intSecID . ',1,0) AS parent';
		$arrWhere	= $this->arrRelWhereData;
		$arrWhere[]	= '(rel_sec_sec.sec_config_id = ' . $this->intSecID . ' OR rel_sec_sec.child_id = ' . $intSecID . ')';

		/**
		 * @todo set PROJECT_ID
		 */
		$this->objRelField = $this->objModel->select($arrFields,'sec_config',$this->arrRelJoinData,$arrWhere,$this->arrRelOrderData,$this->arrRelGroupData);
		
		if($this->objRelField === false) return false; else return true;
	}
	
	/**
	 * Gets Field List data array
	 *
	 * @return	object
	 *
	 * @since 	2013-02-16
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function getFieldList($intSecID = 0) {
		if(empty($intSecID)) $intSecID = $this->intSecID;
		
		// Gets Struct Field list
		/**
		 * @todo set PROJECT_ID
		 */
		$this->objModel->arrTable		= $this->arrTable;
		$this->objModel->arrFieldList	= $this->arrFieldList;
		$this->objModel->arrJoinList	= $this->arrJoinList;
		$this->objModel->arrWhereList	= $this->arrWhereList;
		$this->objModel->arrWhereList[]	= 'sec_config.id = ' . $intSecID;
		$this->objModel->arrGroupList	= $this->arrGroupList;
		$this->objModel->arrOrderList	= $this->arrOrderList;
		$arrTempList = $this->objModel->getList();
		
		// Gets Relationship Field list
		/**
		 * @todo set PROJECT_ID
		 */
		$this->objModel->arrFieldList	= $this->arrRelFieldData;
		$this->objModel->arrJoinList	= $this->arrRelJoinData;
		$this->objModel->arrWhereList	= $this->arrRelWhereData;
		$this->objModel->arrWhereList[]	= 'sec_config.id = ' . $intSecID;
		$this->objModel->arrGroupList	= $this->arrRelGroupData;
		$this->objModel->arrOrderList	= $this->arrRelOrderData;
		$arrTempList = array_merge($arrTempList,$this->objModel->getList());
		
		// Merges and orders array data
		$this->objStruct = array();
		foreach($arrTempList AS $objData) {
			$this->objStruct[$objData->field_order] = clone $objData;
		}
		ksort($this->objStruct);
		
		// Assigns smarty var
		$this->objSmarty->assign('objStruct',$this->objStruct);
		
		// Sets fields data type
		$this->setFieldType();
		
		return $this->objStruct;
	}
	
	/**
	 * Gets specific Field data array
	 *
	 * @return	object
	 *
	 * @since 	2013-02-16
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function getFieldData($intFieldID = 0) {
		if(empty($intFieldID)) $intFieldID = $this->intFieldID;
		
		// Defines field type DYNAMIC || RELATIONSHIP
		$intType = $this->objModel->recordExists('type','sec_config_order','field_id = "' . $intFieldID . '"',true);
		
		// Relationship field
		if($intType == 2) {
			$this->objModel->arrFieldData	= $this->arrRelFieldData;
			$this->objModel->arrJoinData	= $this->arrRelJoinData;
			$this->objModel->arrWhereData	= $this->arrRelWhereData;
			$this->objModel->arrWhereData[]	= 'rel_sec_sec.id = {id}';
			$this->objModel->arrGroupData	= $this->arrRelGroupData;
			$this->objModel->arrOrderData	= $this->arrRelOrderData;
			
			$this->objField = $this->objModel->getData($intFieldID);
	
			// Struct field
		} else {
			$this->objModel->arrWhereData[]	= 'rel_sec_struct.id = {id}';
			$this->objField = $this->objModel->getData($intFieldID);
		}
		
		return $this->objField;
	}
	
	/**
	 * Sets CONTENT URL LINK
	 * 
	 * @param	string $strValue DB value
	 * 
	 * @return	string
	 *
	 * @since 	2013-01-23
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function configContentLink($strValue) {
		if(strpos($strValue,'.')) {
			if(is_numeric($strValue)) {
				$arrLink = explode('.',$strValue);
				$objSection = $this->objModel->getSectionConfig($arrLink[0]);
	
				// Sets URL path
				switch($objSection->static) {
					case 1:
						if($objSection->home == 1) {
							$strValue = HTTP_STATIC . $objSection->static_filename . '.htm';
						} else {
							$strValue = HTTP_STATIC . Controller::permalinkSyntax($objSection->table) . '/' . $objSection->static_filename . '_' . $arrLink[1] . '.htm';
						}
						break;
	
					case 0:
					default:
						$strValue = HTTP_DYNAMIC . 'index.php?' . VAR_SECTION . '=' . $arrLink[0] . '&' . VAR_LANGUAGE . '=' . LANGUAGE . '&' . VAR_CONTENT . '=' . $arrLink[1];
					break;
				}
			} else {
				return $strValue;
			}
		} else {
			// MEDIA GALLERY
			return $strValue;
		}
	}
	
	/**
	 * Checks field's sufyx and sets $arrData and $arrPrintData values
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * @param	mixed	$objField		Array where array_value => DB field's name OR null to get Applr SECTION defaults
	 * @param	integer	$intReturnMode	0 => boolean; 1 => $this->objData values; 2 => $this->objPrintData values. If 1 or 2, resets $this->objData ans $this->objPrintData values to NULL on return
	 * @param	boolean	$boolReturnList	Defines if $objReturn is content specific or data list 
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 * @todo	Verify $mxdData type array | object and otimize method data struct and verification
	 */
	public function setupFieldSufyx($mxdContent,$objField = null,$intReturnMode = 1,$boolReturnList = false) {
		if(!is_array($mxdContent) && !is_object($mxdContent)) return false;
		$mxdFirstChild = reset($mxdContent);
		if( empty($mxdFirstChild) || (!is_array($mxdFirstChild) && !is_object($mxdFirstChild)) ) $mxdContent = array($mxdContent);
		if(!is_numeric($intReturnMode) || $intReturnMode < 0 || $intReturnMode > 2) return false;
		
		// Forces $this->objField to assume method's $objField value
		if(!is_null($objField)) $this->objField = (array) $objField;
		
		// Reset data variables
		$this->objData		= array();
		$this->objPrintData	= array();
		
		#echo '<pre>'; print_r($this->arrRelContent);
		
		foreach($mxdContent AS $intDataKey => $mxdData) {
			$this->arrControl = array();
			foreach($this->objField AS $mxdKey => &$strField) {
				$this->objData[$intDataKey]->$strField = (is_array($mxdData) ? (isset($mxdData[$strField]) ? $mxdData[$strField] : '') : (isset($mxdData->$strField) ? $mxdData->$strField : ''));
				
				if(in_array($strField,array('lang_content','usr_data_id','sys_language_id','date_create','date_publish','date_expire','active'))) {
					$strTemp = $strField;
				} else {
					$strTemp = end(explode('_',$strField));
				}
					
				switch($strTemp) {
					case 'embed':
						// VIMEO
						if(strpos($this->objData[$intDataKey]->$strField,'vimeo') !== false) {
							// Gets Vimeo code
							$arrData = explode('/',$this->objData[$intDataKey]->$strField);
							$arrData = array_reverse($arrData);
							$strCode = (!empty($arrData[0]) ? $arrData[0] : $arrData[1]);
							
							$this->objPrintData[$intDataKey]->$strField->original	= $this->objData[$intDataKey]->$strField;
							$this->objPrintData[$intDataKey]->$strField->embed_uri	= 'https://player.vimeo.com/video/' . $strCode;
						} else {
							
						}
					break;
					
					case 'currency':
						switch($this->objData[$intDataKey]->$strField) {
							default:
							case 0:
								$this->objPrintData[$intDataKey]->$strField->intval		= 0;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'R$';
							break;
							
							case 1:
								$this->objPrintData[$intDataKey]->$strField->intval		= 1;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'USD';
							break;
							
							case 2:
								$this->objPrintData[$intDataKey]->$strField->intval		= 2;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'EUR';
							break;
						}
					break;
					
					case 'treatment':
						switch($this->objData[$intDataKey]->$strField) {
							default:
							case 0:
								$this->objPrintData[$intDataKey]->$strField->intval		= 0;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Sr.';
							break;
							
							case 1:
								$this->objPrintData[$intDataKey]->$strField->intval		= 1;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Mr.';
							break;
							
							case 2:
								$this->objPrintData[$intDataKey]->$strField->intval		= 2;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Mrs.';
							break;
							
							case 3:
								$this->objPrintData[$intDataKey]->$strField->intval		= 3;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Mss.';
							break;
						}
					break;
					
					case 'period':
						switch($this->objData[$intDataKey]->$strField) {
							default:
							case 0:
								$this->objPrintData[$intDataKey]->$strField->intval		= 0;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Morning';
							break;
							
							case 1:
								$this->objPrintData[$intDataKey]->$strField->intval		= 1;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Afternoon';
							break;
							
							case 2:
								$this->objPrintData[$intDataKey]->$strField->intval		= 2;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'Night';
							break;
						}
					break;
					
					case 'sex':
						switch($this->objData[$intDataKey]->$strField) {
							default:
							case 0:
								$this->objPrintData[$intDataKey]->$strField->intval		= 0;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'F';
							break;
							
							case 1:
								$this->objPrintData[$intDataKey]->$strField->intval		= 1;
								$this->objPrintData[$intDataKey]->$strField->formatted	= 'M';
							break;
						}
					break;
					
					case 'pwd':
						$this->objPrintData[$intDataKey]->$strField->original	= $this->objData[$intDataKey]->$strField;
						$this->objPrintData[$intDataKey]->$strField->md5			= md5($this->objData[$intDataKey]->$strField);
					break;
					
					case 'phone':
						if(!empty($this->objData[$intDataKey]->$strField)) 	{
							$idxDDD = $strField . '_ddd';
							$intDDD = (is_array($mxdData) ? $mxdData[$idxDDD] : $mxdData->$idxDDD);
							
							if(!empty($intDDD)) {
								$this->objPrintData[$intDataKey]->$strField->intval		= Controller::onlyNumbers($intDDD . $this->objData[$intDataKey]->$strField);
								$this->objPrintData[$intDataKey]->$strField->formatted	= '(' . $intDDD . ')' . $this->objData[$intDataKey]->$strField;
								$this->objPrintData[$intDataKey]->$strField->original	= array($intDDD,$this->objData[$intDataKey]->$strField);
							} else {
								$intDDD 	= substr($this->objData[$intDataKey]->$strField,0,2);
								$intPhone 	= substr($this->objData[$intDataKey]->$strField,2);
	
								$this->objPrintData[$intDataKey]->$strField->intval		= Controller::onlyNumbers($intDDD . $intPhone);
								$this->objPrintData[$intDataKey]->$strField->formatted	= (!empty($intDDD) ? '(' . $intDDD . ')' : '') . $intPhone;
								$this->objPrintData[$intDataKey]->$strField->original	= array($intDDD,$intPhone);
							}
							
							$this->objData[$intDataKey]->$strField						= $this->objPrintData[$intDataKey]->$strField->intval;
						} else {
							$this->objPrintData[$intDataKey]->$strField = '';
						}
					break;
					
					case 'number':
					case 'cpf':
					case 'rg':
					case 'cnpj':
						$this->objPrintData[$intDataKey]->$strField->original	= $this->objData[$intDataKey]->$strField;
						$this->objPrintData[$intDataKey]->$strField->intval		= Controller::onlyNumbers($this->objData[$intDataKey]->$strField); 
						$this->objData[$intDataKey]->$strField					= $this->objPrintData[$intDataKey]->$strField->intval;
					break;
					
					case 'date':
					case 'Day':
					case 'Month':
					case 'Year':
						$strField	= str_replace(array('_Day','_Month','_Year'),'',$strField);
						
						if(empty($this->arrControl[$strField])) {
							$this->arrControl[$strField] = true;
							
							$strDay		= $strField . '_Day';
							$strMonth	= $strField . '_Month';
							$strYear	= $strField . '_Year';
							
							if(!empty($this->objData[$intDataKey]->$strField)) {
								$arrDate	= explode('-',$this->objData[$intDataKey]->$strField);
								
								$intDay		= (!empty($arrDate[2]) 	? $arrDate[2]	: '00');
								$intMonth	= (!empty($arrDate[1])	? $arrDate[1]	: '00');
								$intYear	= (!empty($arrDate[0])	? $arrDate[0]	: '0000');
							} else {
								$intDay		= (is_array($mxdData) ? (!empty($mxdData[$strDay]) 		? $mxdData[$strDay]		: '00') 	: (!empty($mxdData->$strDay) 	? $mxdData->$strDay		: '00') );
								$intMonth	= (is_array($mxdData) ? (!empty($mxdData[$strMonth])	? $mxdData[$strMonth]	: '00') 	: (!empty($mxdData->$strMonth) 	? $mxdData->$strMonth	: '00') );
								$intYear	= (is_array($mxdData) ? (!empty($mxdData[$strYear])		? $mxdData[$strYear]	: '0000') 	: (!empty($mxdData->$strYear) 	? $mxdData->$strYear	: '0000') );
							}
							
							if($intDay > 0 || $intMonth > 0 || $intYear > 0) 	{
								$intDay		= ($intDay	> 0 ? $intDay	: date('d'));
								$intMonth	= ($intMonth> 0 ? $intMonth	: date('m'));
								$intYear	= ($intYear	> 0 ? $intYear	: date('Y'));
						
								$this->objData[$intDataKey]->$strField 							= $intYear.'-'.$intMonth.'-'.$intDay;
								$this->objPrintData[$intDataKey]->$strField->formatted			= (LANGUAGE != 1 ? date('Y-m-d',strtotime($this->objData[$intDataKey]->$strField)) : date('d/m/Y',strtotime($this->objData[$intDataKey]->$strField)));
								$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= $intYear . $intMonth . $intDay;
							} else {
								$this->objData[$intDataKey]->$strField							= '0000-00-00';
								$this->objPrintData[$intDataKey]->$strField->formatted			= (LANGUAGE != 1 ? '0000-00-00' : '00/00/0000');
								$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= null; #$intYear . $intMonth . $intDay;
							}
							$this->objPrintData[$intDataKey]->$strField->original->Day		= $intDay;
							$this->objPrintData[$intDataKey]->$strField->original->Year		= $intMonth;
							$this->objPrintData[$intDataKey]->$strField->original->Month		= $intYear;
						
							unset($this->objData[$intDataKey]->$strDay,$this->objData[$intDataKey]->$strMonth,$this->objData[$intDataKey]->$strYear);
						}
					break;
					
					case 'hour':
					case 'Hour':
					case 'Minute':
					case 'Second':
					case 'time':
						$strField	= str_replace(array('_Hour','_Minute','_Second'),'',$strField);
	
						if(empty($this->arrControl[$strField])) {
							$this->arrControl[$strField] = true;
							
							$strSecond	= $strField . '_Second';
							$strMinute	= $strField . '_Minute';
							$strHour	= $strField . '_Hour';
							
							if(!empty($this->objData[$intDataKey]->$strField)) {
								$arrTime	= explode(':',$this->objData[$intDataKey]->$strField);
								
								$intSecond	= (!empty($arrTime[2]) 	? $arrTime[2]	: '00');
								$intMinute	= (!empty($arrTime[1])	? $arrTime[1]	: '00');
								$intHour	= (!empty($arrTime[0])	? $arrTime[0]	: '00');
							} else {
								$intSecond	= (is_array($mxdData) ? (!empty($mxdData[$strSecond]) 	? $mxdData[$strSecond]	: '00') 	: (!empty($arrTime[2]) 	? $arrTime[2]	: '00') );
								$intMinute	= (is_array($mxdData) ? (!empty($mxdData[$strMinute])	? $mxdData[$strMinute]	: '00') 	: (!empty($arrTime[1]) 	? $arrTime[1]	: '00') );
								$intHour	= (is_array($mxdData) ? (!empty($mxdData[$strHour])		? $mxdData[$strHour]	: '00') 	: (!empty($arrTime[0]) 	? $arrTime[0]	: '00') );
							}
							
							if($intSecond > 0 || $intMinute > 0 || $intHour > 0) 	{
								$intSecond		= ($intSecond	> 0 ? $intSecond	: date('H'));
								$intMinute	= ($intMinute> 0 ? $intMinute	: date('i'));
								$intHour	= ($intHour	> 0 ? $intHour	: date('s'));
						
								$this->objData[$intDataKey]->$strField 							= $intHour.':'.$intMinute.':'.$intSecond;
								$this->objPrintData[$intDataKey]->$strField->formatted			= date('H:i:s',strtotime($this->objData[$intDataKey]->$strField));
								$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= $intHour . $intMinute . $intSecond;
							} else {
								$this->objData[$intDataKey]->$strField							= '00:00:00';
								$this->objPrintData[$intDataKey]->$strField->formatted			= '00:00:00';
								$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= $intHour . $intMinute . $intSecond;
							}
							$this->objPrintData[$intDataKey]->$strField->original->Hour		= $intHour;
							$this->objPrintData[$intDataKey]->$strField->original->Minute	= $intMinute;
							$this->objPrintData[$intDataKey]->$strField->original->Second	= $intSecond;
						}
						
						unset($this->objData[$intDataKey]->$strSecond,$this->objData[$intDataKey]->$strMinute,$this->objData[$intDataKey]->$strHour);
					break;
					
					case 'date_create':
					case 'date_publish':
					case 'date_expire':
						$arrTemp	= explode(' ',$this->objData[$intDataKey]->$strField);
						$arrDate	= explode('-',$arrTemp[0]);
						$arrTime	= explode(':',$arrTemp[1]);
	
						if($arrDate[0] > 0 || $arrDate[1] > 0 || $arrDate[2] > 0) 	{
							$this->objPrintData[$intDataKey]->$strField->formatted->date		= (LANGUAGE != 1 ? date('Y-m-d',strtotime($arrTemp[0])) : date('d/m/Y',strtotime($arrTemp[0])));
							$this->objPrintData[$intDataKey]->$strField->original->date		= $arrTemp[0];
							$this->objPrintData[$intDataKey]->$strField->original->time		= $arrTemp[1];
							$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= str_replace(array('-',':'),'',$arrTemp[0]) . str_replace(array('-',':'),'',$arrTemp[1]);
						} else {
							$this->objPrintData[$intDataKey]->$strField->formatted->date		= (LANGUAGE != 1 ? '0000-00-00' : '00/00/0000');
							$this->objPrintData[$intDataKey]->$strField->original->date		= $arrTemp[0];
							$this->objPrintData[$intDataKey]->$strField->original->time		= $arrTemp[1];
							$this->objPrintData[$intDataKey]->$strField->original->Timestamp	= null; #$intYear . $intMonth . $intDay;
						}
						
						if($arrTime[0] > 0 || $arrTime[1] > 0 || $arrTime[2] > 0) 	{
							$this->objPrintData[$intDataKey]->$strField->formatted->time		= date('H:i:s',strtotime($arrTemp[1]));
						} else {
							$this->objPrintData[$intDataKey]->$strField->formatted->time		= '00:00:00';
						}
					break;
					
					case 'permalink':
						// Sets permalink string
						$strField_URL = 'url_' . $strField;
						$strPermalink = Controller::permalinkSyntax($this->objData[$intDataKey]->$strField);
						$objData = $this->objModel->select('permalink',$this->objSection->table_name,array(),'permalink LIKE "' . $strPermalink . '%" AND sys_language_id = "' . LANGUAGE . '"');
						$intCount = 1;
						foreach($objData AS $strCount) {
							if(is_string($strCount) && $strCount != '') {
								$strTemp = substr_replace($strCount,'',strrpos($strCount,'-'));
								if($strPermalink == $strCount || $strPermalink == $strTemp) $intCount++;
							}
						}
						if($intCount > 1) $strPermalink .= '-'.$intCount;
						
						$this->objData[$intDataKey]->$strField = $this->objPrintData[$intDataKey]->$strField = $strPermalink;
						$this->objPrintData[$intDataKey]->$strField_URL = HTTP . $this->objSection->permalink . '/' . $strPermalink;
					break;
					
					case 'check':
					case 'checkbox':
						if(empty($this->objData[$intDataKey]->$strField)) {
							$this->objData[$intDataKey]->$strField 		= null;
							$this->objPrintData[$intDataKey]->$strField 	= false;
						} else {
							$this->objPrintData[$intDataKey]->$strField 	= $this->objData[$intDataKey]->$strField;
						}
					break;
					
					case 'rel':
					case 'link':
						$this->objPrintData[$intDataKey]->$strField->original	= $this->objData[$intDataKey]->$strField;
						$this->objPrintData[$intDataKey]->$strField->url 		= $this->configContentLink($this->objData[$intDataKey]->$strField);
					break;
					
					case 'cep':
					case 'zipcode':
						$this->objPrintData[$intDataKey]->$strField->original	= $this->objData[$intDataKey]->$strField;
						$this->objPrintData[$intDataKey]->$strField->intval		= Controller::onlyNumbers($this->objData[$intDataKey]->$strField); 
						$this->objPrintData[$intDataKey]->$strField->formatted	= (!empty($this->objData[$intDataKey]->$strField) ? substr($this->objData[$intDataKey]->$strField,0,5) . '-' . substr($this->objData[$intDataKey]->$strField,5,3) : '');
						$this->objData[$intDataKey]->$strField					= $this->objPrintData[$intDataKey]->$strField->intval;
					break;
					
					case 'upload':
					case 'old':
						$strField 	= str_replace('_old','',$strField);
						$strOld		= $strField . '_old';
						
						if(empty($this->arrControl[$strField])) {
							$this->arrControl[$strField] = true;
							if(isset($_FILES[$strField]) && !empty($_FILES[$strField]['name'])) {
								$objUpload = new uploadFile_Controller($strField,ROOT_UPLOAD . $this->objSection->permalink);
								$objReturn = $objUpload->uploadFile();
								
								$this->objData[$intDataKey]->$strField 					= str_replace(DIR_UPLOAD,'',$objReturn);
								$this->objPrintData[$intDataKey]->$strField->original 	= $this->objData[$intDataKey]->$strField;
								$this->objPrintData[$intDataKey]->$strField->uri 		= HTTP_UPLOAD . $this->objPrintData[$intDataKey]->$strField->original;
							} else {
								$strOldField = $strField.'_old';
								
								$this->objData[$intDataKey]->$strField = (is_array($mxdData) ? $mxdData[$strOldField] : $mxdData->$strOldField);
								if(empty($this->objData[$intDataKey]->$strField)) {
									$this->objData[$intDataKey]->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
								}
								
								$this->objPrintData[$intDataKey]->$strField->original 	= (empty($this->objData[$intDataKey]->$strField) ? '' : $this->objData[$intDataKey]->$strField);
								$this->objPrintData[$intDataKey]->$strField->uri 		= (empty($this->objPrintData[$intDataKey]->$strField->original) ? '' : HTTP_UPLOAD . $this->objPrintData[$intDataKey]->$strField->original);
							}
						} else {
							$this->objPrintData[$intDataKey]->$strField->original	= str_replace(HTTP_UPLOAD,'',$this->objData[$intDataKey]->$strField);
							$this->objPrintData[$intDataKey]->$strField->uri 		= (empty($this->objPrintData[$intDataKey]->$strField->original) ? '' : HTTP_UPLOAD . $this->objPrintData[$intDataKey]->$strField->original);
						}
						
						unset($this->objData[$intDataKey]->$strOld);
					break;
					
					case 'img':
					case 'image':
						if(empty($this->arrControl[$strField])) {
							$this->arrControl[$strField] = true;
							
							if(isset($_FILES[$strField]) && !empty($_FILES[$strField]['name'])) {
								// Uploads file
								$objUpload = new uploadFile_Controller($strField,ROOT_IMAGE . Controller::permalinkSyntax($this->objSection->table));
								$this->objData[$intDataKey]->$strField['file'] = $objUpload->uploadFile();
								
								// Inserts DB registry
								/**
								 * @todo set PROJECT_ID
								 */
								$arrData = array(
												'media_gallery_id'		=> null,
												'usr_data_id'			=> $this->intUserID,
												'type'					=> true,
												'name'					=> $this->objData[$intDataKey]->$strField['name'],
												'author'				=> $this->objData[$intDataKey]->$strField['author'],
												'label'					=> $this->objData[$intDataKey]->$strField['label'],
												'filepath'				=> $this->objData[$intDataKey]->$strField['file'],
												'filepath_thumbnail'	=> $this->objData[$intDataKey]->$strField['file'],
												'filepath_streaming'	=> null
											);
								$objInsert = $this->objModel->insert('media_data',$arrData,true);
								
								// Sets sys vars
								$this->objPrintData[$intDataKey]->$strField->id		= $objInsert;
								$this->objPrintData[$intDataKey]->$strField->file	= $this->objData[$intDataKey]->$strField['file'];
								$this->objPrintData[$intDataKey]->$strField->path	= $objUpload->strPath;
								$this->objPrintData[$intDataKey]->$strField->info	= $arrData;
							} else {
								$this->objPrintData[$intDataKey]->$strField = $this->objData[$intDataKey]->$strField;
							}
						} else {
							$this->objPrintData[$intDataKey]->$strField = $this->objData[$intDataKey]->$strField;
						}
					break;
					
					case 'video':
						if(empty($this->arrControl[$strField])) {
							$this->arrControl[$strField] = true;
							
							if(isset($_FILES[$strField]) && !empty($_FILES[$strField]['name'])) {
								// Uploads file
								$objUpload = new uploadFile_Controller($strField,ROOT_VIDEO . Controller::permalinkSyntax($this->objSection->table));
								$this->objData[$intDataKey]->$strField['file'] = $objUpload->uploadFile();
								
								// Inserts DB registry
								/**
								 * @todo set PROJECT_ID
								 */
								$arrData = array(
												'media_gallery_id'		=> null,
												'usr_data_id'			=> $this->intUserID,
												'type'					=> false,
												'name'					=> $this->objData[$intDataKey]->$strField['name'],
												'author'				=> $this->objData[$intDataKey]->$strField['author'],
												'label'					=> $this->objData[$intDataKey]->$strField['label'],
												'filepath'				=> $this->objData[$intDataKey]->$strField['file'],
												'filepath_thumbnail'	=> null,
												'filepath_streaming'	=> $this->objData[$intDataKey]->$strField['file']
											);
								$objInsert = $this->objModel->insert('media_data',$arrData,true);
								
								// Sets sys vars
								$this->objPrintData[$intDataKey]->$strField->id		= $objInsert;
								$this->objPrintData[$intDataKey]->$strField->file	= $this->objData[$intDataKey]->$strField['file'];
								$this->objPrintData[$intDataKey]->$strField->path	= $objUpload->strPath;
								$this->objPrintData[$intDataKey]->$strField->info	= $arrData;
							} else {
								$this->objPrintData[$intDataKey]->$strField = $this->objData[$intDataKey]->$strField;
							}
						} else {
							$this->objPrintData[$intDataKey]->$strField = $this->objData[$intDataKey]->$strField;
						}
					break;
					
					default:
						$this->objPrintData[$intDataKey]->$strField = $this->objData[$intDataKey]->$strField;
					break;
					
					/***********************************/
					/* NEEDS APPLR! SYSTEM DEFINITIONS */
					/***********************************/
					
					case 'multitext':
						if(is_array($this->objData[$intDataKey]->$strField)) {
							$this->objPrintData[$intDataKey]->$strField	= $this->objData[$intDataKey]->$strField;
							$this->objData[$intDataKey]->$strField		= implode("#MULTITEXT#",(is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField));
						} else {
							$this->objData[$intDataKey]->$strField		= null;
							$this->objPrintData[$intDataKey]->$strField	= '';
						}
					break;
						
					case 'thumbnail':
					break;
					
					case 'poll':
					break;
					
					case 'quiz':
					break;
					
					case 'mediagal':
					case 'mediagallery':
					break;
					
					case 'country':
					break;
					
					case 'state':
					break;
					
					case 'city':
					break;
				}
			}
		}
	
		switch($intReturnMode) {
			case 0:
			default:
				return false;
			break;
			
			case 1:
				$objReturn 			= ($boolReturnList ? clone (object) $this->objData : clone (object) reset($this->objData));
				$this->objData		= null;
				return $objReturn;
			break;
			
			case 2:
				$objReturn 			= ($boolReturnList ? clone (object) $this->objPrintData : clone (object) reset($this->objPrintData));
				$this->objPrintData	= null;
				
				return $objReturn;
			break;
		}
	}

	/**
	 * Insert main content and sets $this->contentID as last insert ID value
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function insertMainContent($mxdData) {
		if(!is_array($mxdData)) return false;

		if( ($this->objData = $this->setupFieldSufyx($mxdData,array_keys($mxdData))) !== false) {
			if( ($this->intContentID = $this->objModel->insert($this->objSection->table_name,(array) $this->objData,true)) !== false) {
				return true;
			} else {
				return false;
			}
		} else {
			define('ERROR_MSG','$this->setupFieldSufyx error!');
			$this->intContentID = null;
			return false;
		}
	}

	/**
	 * Updates main content and sets $this->contentID as last insert ID value
	 * 
	 * @param	integer	$intContentID	Content's ID on DB
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function updateMainContent($intContentID,$mxdData) {
		if(!is_array($mxdData)) return false;
		
		if( ($this->objData = $this->setupFieldSufyx($mxdData,array_keys($mxdData))) !== false) {
			if($this->objModel->update($this->objSection->table_name,(array) $this->objData,$this->objSection->table_name.'.id = ' . $intContentID) !== false) {
				$this->intContentID = $intContentID;
				return true;
			} else {
				define('ERROR_MSG','$this->updateMainContent::update error!');
				$this->intContentID = null;
				return false;
			}
		} else {
			define('ERROR_MSG','$this->setupFieldSufyx error!');
			$this->intContentID = null;
			return false;
		}
	}
	
	/**
	 * Insert relationship contents and sets $this->arrRelContent with related NAME attr
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function insertRelContent($mxdData) {
		if(!is_array($mxdData)) return false;
		if($this->objRelField === false) return false;
		
		foreach($this->objRelField AS $mxdKey => $objRel) {
			
			// Sets relationship params
			if($this->setRelParams($objRel)) {
				
				// Setups rel fields
				if($this->setupRelField($mxdData,$objRel->field_name)) {
					
					foreach($this->arrRelData[$objRel->field_name] AS $intRelID) {
						// Inserts new rel data
						$arrInsertData = ($objRel->parent ? array($this->objRelParams->strParentField => $this->intContentID, $this->objRelParams->strChildField => $intRelID) : array($this->objRelParams->strParentField => $intRelID, $this->objRelParams->strChildField => $this->intContentID));
						
						if( ($objInsert = $this->objModel->insert($objRel->table_name,$arrInsertData)) !== false ) {
							#$this->arrRelContent[$objRel->field_name][] = $this->recordExists('name', ($objRel->parent ? $this->objRelParams->strParentTable : $this->objRelParams->strChildTable),'id = ' . $intRelID,true);
						} else {
							define('ERROR_MSG','$this->insertRelContent::insert error on ' . $mxdKey . '!');
							#$this->arrRelContent[$objRel->field_name] = null;
							echo '4';
							return false;
						}
					}
				} else {
					define('ERROR_MSG','$this->setupRelField error on ' . $objRel->field_name . ' : ' . $objRel->table_name . '!');
					#$this->arrRelContent[$objRel->field_name] = null;
					return false;
				}
			} else {
				define('ERROR_MSG','$this->setRelParams error on ' . $mxdKey . '!');
				#$this->arrRelContent[$objRel->field_name] = null;
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Updates relationship contents and sets $this->arrRelContent with related NAME attr
	 * 
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function updateRelContent($mxdData) {
		if(!is_array($mxdData)) return false;
		if($this->objRelField === false) return false;
		
		foreach($this->objRelField AS $mxdKey => $objRel) {
			
			// Sets relationship params
			if($this->setRelParams($objRel)) {
				// Deletes previous related content
				if($this->objModel->delete($objRel->table_name,$this->objRelParams->strWhere) !== false) {
					// Setups rel fields
					if($this->setupRelField($mxdData,$objRel->field)) {
						foreach($this->arrRelData[$objRel->field] AS $intRelID) {
							// Inserts new rel data
							if($this->objModel->insert($objRel->table_name, ($objRel->parent ? array($this->objRelParams->strParentField => $this->intContentID, $this->objRelParams->strChildField => $intRelID) : array($this->objRelParams->strParentField => $intRelID, $this->objRelParams->strChildField => $this->intContentID)) ) !== false) {
								#$this->arrRelContent[$objRel->field][] = $this->recordExists('name', ($objRel->parent ? $this->objRelParams->strParentTable : $this->objRelParams->strChildTable),'id = ' . $intRelID,true);
							} else {
								define('ERROR_MSG','$this->updateRelContent::insert error on ' . $mxdKey . '!');
								#$this->arrRelContent[$objRel->field] = null;
								return false;
							}
						}
					} else {
						define('ERROR_MSG','$this->setupRelField error on ' . $objRel->field . ' : ' . $objRel->table_name . '!');
						#$this->arrRelContent[$objRel->field] = null;
						return false;
					}
				} else {
					define('ERROR_MSG','$this->updateRelContent::delete error!');
					#$this->arrRelContent[$objRel->field] = null;
					return false;
				}
			} else {
				define('ERROR_MSG','$this->setRelParams error on ' . $mxdKey . '!');
				#$this->arrRelContent[$objRel->field] = null;
				return false;
			}
		}
		
		return true;
	}
	
	private function setRelParams($objRel) {
		if(!is_object($objRel) || !isset($objRel->table_name) || !isset($objRel->parent)) 	return false;
		if(empty($objRel->table_name) || strpos($objRel->table_name,'sec_rel_') !== 0) 		return false;
		if($objRel->parent != 0 && $objRel->parent != 1) 									return false;
		
		$arrTables = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
		if($objRel->parent) {
			$this->objRelParams->strParentTable = $arrTables[0];
			$this->objRelParams->strParentField	= 'parent_id';
			$this->objRelParams->strChildTable	= $arrTables[1];
			$this->objRelParams->strChildField	= 'child_id';
			
			$this->objRelParams->strWhere		= $this->objRelParams->strParentField . ' = ' . $this->intContentID;
		} else {
			$this->objRelParams->strParentTable = $arrTables[1];
			$this->objRelParams->strParentField	= 'parent_id';
			$this->objRelParams->strChildTable	= $arrTables[0];
			$this->objRelParams->strChildField	= 'child_id';
			
			$this->objRelParams->strWhere		= $this->objRelParams->strChildField . ' = ' . $this->intContentID;
		}
		return true;
	}
	
	protected function setRelContent($boolRelField = false) {
		$tmpObj = (!$boolRelField ? $this->arrRelContent : $this->objRelField);
		
		$intRel = 0;
		foreach($tmpObj AS $objRel) {
			$arrTables 		= explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));
			$strTable 		= ($objRel->parent ? $arrTables[1] : $arrTables[0]);
			$strTableAlias	= ($objRel->parent ? (strpos($objRel->table_name,'_parent') && strpos($objRel->table_name,'_child') ? $strTable . '_child' : $strTable) : (strpos($objRel->table_name,'_parent') && strpos($objRel->table_name,'_child') ? $strTable . '_parent' : $strTable));
				
			$arrChildField = explode(',',$objRel->child_fields);
			foreach($arrChildField AS &$strField) {
				if($strField != 'permalink') {
					$strField = '\"' . $strField . '\":\"",IF(' . $strTableAlias . '.' . $strField. ' IS NULL,"",' . $strTableAlias . '.' . $strField. '),"\"';
				} else {
					$strAttrPermalink = 'IF(' . $strTableAlias . '.' . $strField. ' IS NULL,"", CONCAT("' . $objRel->child_section_permalink . '/",' . $strTableAlias . '.' . $strField. ') )';
					$strField = '\"' . $strField . '\":\"",' . $strAttrPermalink . ',"\"';
				}
			}
			$arrChildField[] = '\"url_permalink\":\"", CONCAT("' . HTTP . '",' . $strAttrPermalink . '),"\"';
		
			if($objRel->parent) {
				$this->objModel->arrFieldData[$objRel->field_name]	= $this->objModel->arrFieldList[$objRel->field_name]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",' . $strTableAlias . '.id,"\",\"value\":\"",' . $strTableAlias . '.' . $objRel->field_rel. ',"\",' . implode(',',$arrChildField) . '}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
				$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[$strTableAlias]		= $this->objModel->arrJoinList[$strTableAlias]			= 'LEFT JOIN ' . $strTable . ' AS ' . $strTableAlias . ' ON rel_tbl_' . $intRel . '.child_id = ' . $strTableAlias . '.id';
			} else {
				$this->objModel->arrJoinData['rel_tbl_' . $intRel]	= $this->objModel->arrJoinList['rel_tbl_' . $intRel]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = ' . $this->objModel->strTable . '.id';
				$this->objModel->arrJoinData[$strTableAlias]		= $this->objModel->arrJoinList[$strTableAlias]			= 'LEFT JOIN ' . $strTable . ' AS ' . $strTableAlias . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $strTableAlias . '.id';
			}
				
			$intRel++;
		}
	}
	
	/**
	 * Checks for relationship sent data and sets $this->arrRelData to insert
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * @param	string	$strRelField	DB::rel_sec_sec.field_name value
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	private function setupRelField($mxdData,$strRelField) {
		if(!is_array($mxdData)) return false;
		if(!is_string($strRelField) || empty($strRelField)) return false;
		
		$mxdData = (array) $mxdData;
		$this->arrRelData = array();
		
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
		
		return true;
	}

	/**
	 * Inserts SECTION data
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function insert($mxdData) {
		if(!is_array($mxdData)) return false;
		
		if($this->insertMainContent($mxdData)) {
			if($this->insertRelContent($mxdData)) {
				return true;
			} else {
				define('ERROR_MSG','$this->insertRelContent error!');
				return false;
			}
		} else {
			define('ERROR_MSG','$this->insertMainContent error!');
			return false;
		}
	}

	/**
	 * Updates SECTION data
	 * 
	 * @param	mixed	$mxdData		POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function update($mxdData) {
		if(!is_array($mxdData)) return false;
		
		if($this->updateMainContent($mxdData)) {
			if($this->updateRelContent($mxdData)) {
				return true;
			} else {
				define('ERROR_MSG','$this->updateRelContent error!');
				return false;
			}
		} else {
			define('ERROR_MSG','$this->updateMainContent error!');
			return false;
		}
	}
	
	protected function setFieldType() {
		// Sets $arrFieldType
		foreach($this->objStruct AS $objField) {
			switch($objField->fieldtype) {
				case 'clob':
				case 'text|fixed':
				case 'text':
				case 'blob':
				case 'char':
				case 'varchar':
				case 'enum':
					$strType = ($objField->suffix == 'mail' ? 'email' : 'string');
					break;
	
				case 'boolean':
					$strType = 'boolean';
					break;
	
				case 'integer':
				case 'decimal':
				case 'float':
				case 'tinyint':
				case 'bigint':
				case 'double':
				case 'year':
					$strType = 'numeric_clearchar';
					break;
	
				case '0':
				case '1':
					$strType 				= 'numeric_clearchar';
					$this->arrRelContent[$objField->field_name] 	= clone $objField;
					$this->arrRelContent[$objField->field_name]->parent = ($this->intSecID == $objField->sec_config_id ? 1 : 0);
					
					break;
	
				case '2':
					$strType 				= 'array';
					$this->arrRelContent[$objField->field_name] 	= clone $objField;
					$this->arrRelContent[$objField->field_name]->parent = ($this->intSecID == $objField->sec_config_id ? 1 : 0);
					break;
	
				case 'time':
					$strType = '';
					break;
	
				case 'timestamp':
				case 'datetime':
				case 'date':
					$strType = 'date';
					break;
			}
	
			if($objField->mandatory == 0) $strType .= '_empty';
	
			$this->arrFieldType[$objField->field_name] = $strType;
		}
	}
	
	static function isAjaxRequest() {
		if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') ) {
			return true;
		} else {
			return false;
		}
	} 
}
?>