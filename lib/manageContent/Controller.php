<?php
class manageContent_Controller extends CRUD_Controller {
	public		$objSection;
	public		$objField;
	public		$objRelField;
	
	public		$arrRelData;
	public		$objPrintData;
	public		$intContentID;
	public		$arrRelContent;
	
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
	protected	$arrJoinData		= array('JOIN rel_sec_struct','JOIN sec_config_order','JOIN sec_struct');
	protected	$arrWhereData		= array(
											'rel_sec_struct.sec_config_id = sec_config.id',
											'rel_sec_struct.id = sec_config_order.field_id',
											'rel_sec_struct.sec_struct_id = sec_struct.id'
											);
	protected	$arrGroupData		= array('rel_sec_struct.id');
	protected	$arrOrderData		= array('rel_sec_struct.id');
	
	protected	$arrRelFieldData	= array('rel_sec_sec.*','sec_config_order.field_order','sec_config_order.type');
	protected	$arrRelJoinData		= array('JOIN rel_sec_sec','JOIN sec_config_order');
	protected	$arrRelWhereData	= array(
											'rel_sec_sec.sec_config_id = sec_config.id',
											'rel_sec_sec.id = sec_config_order.field_id'
										);
	protected	$arrRelGroupData	= array('rel_sec_sec.id');
	protected	$arrRelOrderData	= array('rel_sec_sec.id');
	
	/**
	 * Class constructor, sets $this->intSecID and instantiates $this->objConn
	 * 
	 * @param	integer	$intSecID	Section ID
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
	public function __construct($intSecID = 0) {
		parent::__construct(false);
		
		$this->objSection	= $this->objModel->getSectionConfig($intSecID);
		
		if(!empty($this->objSection)) {
			$this->setSection($intSecID);
			$this->getSectionFields();
			$this->getRelSectionFields();
		}
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
		
		$this->intSecID = $intSecID;
		
		return true;
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
		$this->objField = $this->objModel->select('field_name','rel_sec_struct',array(),'sec_config_id = ' . $this->intSecID);
		
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
	public function getRelSectionFields() {
		$this->objRelField = $this->objModel->select(array('field_name','table_name','IF(sec_config_id = ' . $this->intSecID . ',1,0) AS parent'),'rel_sec_sec',array(),'sec_config_id = ' . $this->intSecID . ' OR child_id = ' . $this->intSecID);
		
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
		$this->objModel->arrTable		= $this->arrTable;
		$this->objModel->arrFieldList	= $this->arrFieldList;
		$this->objModel->arrJoinList	= $this->arrJoinList;
		$this->objModel->arrWhereList	= $this->arrWhereList;
		$this->objModel->arrWhereList[]	= 'sec_config.id = ' . $intSecID;
		$this->objModel->arrGroupList	= $this->arrGroupList;
		$this->objModel->arrOrderList	= $this->arrOrderList;
		$arrTempList = $this->objModel->getList();
		
		// Gets Relationship Field list
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
		
		// Relationship field
		if($intFieldID >= 200000) {
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
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 * @todo	Verify $mxdData type array | object and otimize method data struct and verification
	 */
	public function setupFieldSufyx($mxdData,$objField = null,$intReturnMode = 0) {
		if(!is_array($mxdData) && !is_object($mxdData)) return false;
		#if($this->objField === false) return false;
		if(!is_numeric($intReturnMode) || $intReturnMode < 0 || $intReturnMode > 2) return false;
		
		// Forces $this->objField to assume method's $objField value
		if(!is_null($objField)) $this->objField = (array) $objField;
		
		#echo '<pre>'; var_dump($mxdData); var_dump($this->objField); die();
		
		// Reset data variables
		$arrControl			= array();
		$this->objData		= null;
		$this->objPrintData	= null;
		foreach($this->objField AS $mxdKey => &$strField) {
			
			#echo '<pre>'; var_dump($mxdData); var_dump($this->objField); die();
			$this->objData->$strField = (is_array($mxdData) ? (!empty($mxdData[$strField]) ? $mxdData[$strField] : '') : (!empty($mxdData->$strField) ? $mxdData->$strField : ''));
			
			if(in_array($strField,array('date_create','date_publish','date_expire'))) {
				$strTemp = $strField;
			} else {
				$strTemp = end(explode('_',$strField));
			}
			
			switch($strTemp) {
				case 'currency':
					switch($this->objData->$strField) {
						default:
						case 0:
							$this->objPrintData->$strField->intval		= 0;
							$this->objPrintData->$strField->formatted	= 'R$';
						break;
						
						case 1:
							$this->objPrintData->$strField->intval		= 1;
							$this->objPrintData->$strField->formatted	= 'USD';
						break;
						
						case 2:
							$this->objPrintData->$strField->intval		= 2;
							$this->objPrintData->$strField->formatted	= 'EUR';
						break;
					}
				break;
				
				case 'title':
					switch($this->objData->$strField) {
						default:
						case 0:
							$this->objPrintData->$strField->intval		= 0;
							$this->objPrintData->$strField->formatted	= 'Sr.';
						break;
						
						case 1:
							$this->objPrintData->$strField->intval		= 1;
							$this->objPrintData->$strField->formatted	= 'Mr.';
						break;
						
						case 2:
							$this->objPrintData->$strField->intval		= 2;
							$this->objPrintData->$strField->formatted	= 'Mrs.';
						break;
						
						case 3:
							$this->objPrintData->$strField->intval		= 3;
							$this->objPrintData->$strField->formatted	= 'Mss.';
						break;
					}
				break;
				
				case 'period':
					switch($this->objData->$strField) {
						default:
						case 0:
							$this->objPrintData->$strField->intval		= 0;
							$this->objPrintData->$strField->formatted	= 'Morning';
						break;
						
						case 1:
							$this->objPrintData->$strField->intval		= 1;
							$this->objPrintData->$strField->formatted	= 'Afternoon';
						break;
						
						case 2:
							$this->objPrintData->$strField->intval		= 2;
							$this->objPrintData->$strField->formatted	= 'Night';
						break;
					}
				break;
				
				case 'sex':
					switch($this->objData->$strField) {
						default:
						case 0:
							$this->objPrintData->$strField->intval		= 0;
							$this->objPrintData->$strField->formatted	= 'F';
						break;
						
						case 1:
							$this->objPrintData->$strField->intval		= 1;
							$this->objPrintData->$strField->formatted	= 'M';
						break;
					}
				break;
				
				case 'pwd':
					$this->objPrintData->$strField->original	= $this->objData->$strField;
					$this->objPrintData->$strField->md5			= md5($this->objData->$strField);
				break;
				
				case 'phone':
					if((is_object($mxdData) && !empty($mxdData->$strField)) || (is_array($mxdData) && !empty($mxdData[$strField]))) 	{
						$intPhone = $strField . '_ddd';
						
						if(is_object($mxdData)) {
							if(!empty($mxdData->$intPhone)) {
								$intPhone = $mxdData->$intPhone;
	
								$this->objPrintData->$strField->intval		= Controller::onlyNumbers($intPhone . $mxdData->$strField);
								$this->objPrintData->$strField->formatted	= (!empty($intPhone) ? '(' . $intPhone . ')' : '') . $mxdData->$strField;
								$this->objPrintData->$strField->original	= array($intPhone,$mxdData->$strField);
							} else {
								$intPhone = substr($mxdData->$strField,0,2);
								$mxdData->$strField = substr($mxdData->$strField,2);
	
								$this->objPrintData->$strField->intval		= Controller::onlyNumbers($intPhone . $mxdData->$strField);
								$this->objPrintData->$strField->formatted	= (!empty($intPhone) ? '(' . $intPhone . ')' : '') . $mxdData->$strField;
								$this->objPrintData->$strField->original	= array($intPhone,$mxdData->$strField);
							}
						} else {
							if(!empty($mxdData->$intPhone)) {
								$intPhone = $mxdData[$intPhone];
								
								$this->objPrintData->$strField->intval		= Controller::onlyNumbers($intPhone . $mxdData[$strField]);
								$this->objPrintData->$strField->formatted	= (!empty($intPhone) ? '(' . $intPhone . ')' : '') . $mxdData[$strField];
								$this->objPrintData->$strField->original	= array($intPhone,$mxdData[$strField]);
							} else {
								$intPhone = substr($mxdData[$strField],0,2);
								$mxdData[$strField] = substr($mxdData[$strField],2);
	
								$this->objPrintData->$strField->intval		= Controller::onlyNumbers($intPhone . $mxdData[$strField]);
								$this->objPrintData->$strField->formatted	= (!empty($intPhone) ? '(' . $intPhone . ')' : '') . $mxdData[$strField];
								$this->objPrintData->$strField->original	= array($intPhone,$mxdData[$strField]);
							}
						}
						
						$this->objData->$strField						= $this->objPrintData->$strField->intval;
					} else {
						$this->objData->$strField = $this->objPrintData->$strField = '';
					}
				break;
				
				case 'number':
				case 'cpf':
				case 'rg':
				case 'cnpj':
					$this->objPrintData->$strField->original	= $this->objData->$strField;
					$this->objPrintData->$strField->intval		= Controller::onlyNumbers($this->objData->$strField); 
					$this->objData->$strField					= $this->objPrintData->$strField->intval;
				break;
				
				case 'date':
				case 'Day':
				case 'Month':
				case 'Year':
					$strField	= str_replace(array('_Day','_Month','_Year'),'',$strField);
					
					if(empty($arrControl[$strField])) {
						$arrControl[$strField] = true;
						
						$strDay		= $strField . '_Day';
						$strMonth	= $strField . '_Month';
						$strYear	= $strField . '_Year';
						
						if(!empty($this->objData->$strField)) {
							$arrDate	= explode('-',$this->objData->$strField);
							
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
					
							$this->objData->$strField 							= $intYear.'-'.$intMonth.'-'.$intDay;
							$this->objPrintData->$strField->formatted			= (LANGUAGE != 1 ? date('Y-m-d',strtotime($this->objData->$strField)) : date('d/m/Y',strtotime($this->objData->$strField)));
							$this->objPrintData->$strField->original->Timestamp	= $intYear . $intMonth . $intDay;
						} else {
							$this->objData->$strField							= '0000-00-00';
							$this->objPrintData->$strField->formatted			= (LANGUAGE != 1 ? '0000-00-00' : '00/00/0000');
							$this->objPrintData->$strField->original->Timestamp	= null; #$intYear . $intMonth . $intDay;
						}
						$this->objPrintData->$strField->original->Day		= $intDay;
						$this->objPrintData->$strField->original->Year		= $intMonth;
						$this->objPrintData->$strField->original->Month		= $intYear;
					}
				break;
				
				case 'hour':
				case 'Hour':
				case 'Minute':
				case 'Second':
				case 'time':
					$strField	= str_replace(array('_Hour','_Minute','_Second'),'',$strField);

					if(empty($arrControl[$strField])) {
						$arrControl[$strField] = true;
						
						$intSecond	= $strField . '_Second';
						$intMinute	= $strField . '_Minute';
						$intHour	= $strField . '_Hour';
						
						if(!empty($this->objData->$strField)) {
							$arrTime	= explode(':',$this->objData->$strField);
							
							$intSecond	= (!empty($arrTime[2]) 	? $arrTime[2]	: '00');
							$intMinute	= (!empty($arrTime[1])	? $arrTime[1]	: '00');
							$intHour	= (!empty($arrTime[0])	? $arrTime[0]	: '00');
						} else {
							$intSecond	= (is_array($mxdData) ? (!empty($mxdData[$intSecond]) 	? $mxdData[$intSecond]	: '00') 	: (!empty($arrTime[2]) 	? $arrTime[2]	: '00') );
							$intMinute	= (is_array($mxdData) ? (!empty($mxdData[$intMinute])	? $mxdData[$intMinute]	: '00') 	: (!empty($arrTime[1]) 	? $arrTime[1]	: '00') );
							$intHour	= (is_array($mxdData) ? (!empty($mxdData[$intHour])		? $mxdData[$intHour]	: '00') 	: (!empty($arrTime[0]) 	? $arrTime[0]	: '00') );
						}
						
						if($intSecond > 0 || $intMinute > 0 || $intHour > 0) 	{
							$intSecond		= ($intSecond	> 0 ? $intSecond	: date('H'));
							$intMinute	= ($intMinute> 0 ? $intMinute	: date('i'));
							$intHour	= ($intHour	> 0 ? $intHour	: date('s'));
					
							$this->objData->$strField 							= $intHour.':'.$intMinute.':'.$intSecond;
							$this->objPrintData->$strField->formatted			= date('H:i:s',strtotime($this->objData->$strField));
							$this->objPrintData->$strField->original->Timestamp	= $intHour . $intMinute . $intSecond;
						} else {
							$this->objData->$strField							= '00:00:00';
							$this->objPrintData->$strField->formatted			= '00:00:00';
							$this->objPrintData->$strField->original->Timestamp	= $intHour . $intMinute . $intSecond;
						}
						$this->objPrintData->$strField->original->Hour		= $intHour;
						$this->objPrintData->$strField->original->Minute	= $intMinute;
						$this->objPrintData->$strField->original->Second	= $intSecond;
					}
				break;
				
				case 'date_create':
				case 'date_publish':
				case 'date_expire':
					$arrTemp	= explode(' ',$this->objData->$strField);
					$arrDate	= explode('-',$arrTemp[0]);
					$arrTime	= explode(':',$arrTemp[1]);

					if($arrDate[0] > 0 || $arrDate[1] > 0 || $arrDate[2] > 0) 	{
						$this->objPrintData->$strField->formatted->date		= (LANGUAGE != 1 ? date('Y-m-d',strtotime($arrTemp[0])) : date('d/m/Y',strtotime($arrTemp[0])));
						$this->objPrintData->$strField->original->date		= $arrTemp[0];
						$this->objPrintData->$strField->original->time		= $arrTemp[1];
						$this->objPrintData->$strField->original->Timestamp	= str_replace(array('-',':'),'',$arrTemp[0]) . str_replace(array('-',':'),'',$arrTemp[1]);
					} else {
						$this->objPrintData->$strField->formatted->date		= (LANGUAGE != 1 ? '0000-00-00' : '00/00/0000');
						$this->objPrintData->$strField->original->date		= $arrTemp[0];
						$this->objPrintData->$strField->original->time		= $arrTemp[1];
						$this->objPrintData->$strField->original->Timestamp	= null; #$intYear . $intMonth . $intDay;
					}
					
					if($arrTime[0] > 0 || $arrTime[1] > 0 || $arrTime[2] > 0) 	{
						$this->objPrintData->$strField->formatted->time		= date('H:i:s',strtotime($arrTemp[1]));
					} else {
						$this->objPrintData->$strField->formatted->time		= '00:00:00';
					}
				break;
				
				case 'permalink':
					// Sets permalink string
					$strPermalink = Controller::permalinkSyntax($this->objData->$strField);
					$objData = $this->objModel->select('permalink',$this->objSection->table_name,array(),'permalink LIKE "' . $strPermalink . '%" AND sys_language_id = "' . LANGUAGE . '"');
					$intCount = 1;
					foreach($objData AS $strCount) {
						if(is_string($strCount) && $strCount != '') {
							$strTemp = substr_replace($strCount,'',strrpos($strCount,'-'));
							if($strPermalink == $strCount || $strPermalink == $strTemp) $intCount++;
						}
					}
					if($intCount > 1) $strPermalink .= '-'.$intCount;
					
					$this->objData->$strField = $this->objPrintData->$strField = $strPermalink;
				break;
				
				case 'upload':
				case 'old':
					$strField = str_replace('_old','',$strField);
					if(isset($_FILES[$strField])) {
						$objUpload = new uploadFile_Controller($strField,ROOT_UPLOAD . Controller::permalinkSyntax($this->objSection->name));
						$this->objData->$strField = $this->objPrintData->$strField = $objUpload->uploadFile();
					} else {
						$this->objData->$strField = $this->objPrintData->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
					}
				break;
				
				case 'check':
				case 'checkbox':
					if(empty($this->objData->$strField)) {
						$this->objData->$strField 		= null;
						$this->objPrintData->$strField 	= false;
					} else {
						$this->objPrintData->$strField 	= $this->objData->$strField;
					}
				break;
				
				case 'rel':
				case 'link':
					$this->objPrintData->$strField->original	= $this->objData->$strField;
					$this->objPrintData->$strField->url 		= $this->configContentLink($this->objData->$strField);
				break;
				
				case 'cep':
				case 'zipcode':
					$this->objPrintData->$strField->original	= $this->objData->$strField;
					$this->objPrintData->$strField->intval		= Controller::onlyNumbers($this->objData->$strField); 
					$this->objPrintData->$strField->formatted	= (!empty($this->objData->$strField) ? substr($this->objData->$strField,0,5) . '-' . substr($this->objData->$strField,5,3) : '');
					$this->objData->$strField					= $this->objPrintData->$strField->intval;
				break;
				
				case 'img':
				case 'image':
					if(isset($_FILES[$strField])) {
						// Uploads file
						$objUpload = new uploadFile_Controller($strField,ROOT_IMAGE . Controller::permalinkSyntax($this->objSection->table));
						$this->objData->$strField['file'] = $objUpload->uploadFile();
						
						// Inserts DB registry
						$arrData = array(
										'media_gallery_id'		=> null,
										'usr_data_id'			=> $this->intUserID,
										'type'					=> true,
										'name'					=> $this->objData->$strField['name'],
										'author'				=> $this->objData->$strField['author'],
										'label'					=> $this->objData->$strField['label'],
										'filepath'				=> $this->objData->$strField['file'],
										'filepath_thumbnail'	=> $this->objData->$strField['file'],
										'filepath_streaming'	=> null
									);
						$objInsert = $this->objModel->insert('media_data',$arrData,true);
						
						// Sets sys vars
						$this->objPrintData->$strField->id		= $objInsert;
						$this->objPrintData->$strField->file	= $this->objData->$strField['file'];
						$this->objPrintData->$strField->path	= $objUpload->strPath;
						$this->objPrintData->$strField->info	= $arrData;
					} else {
						$this->objData->$strField = $this->objPrintData->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
					}
				break;
				
				case 'video':
					if(isset($_FILES[$strField])) {
						// Uploads file
						$objUpload = new uploadFile_Controller($strField,ROOT_VIDEO . Controller::permalinkSyntax($this->objSection->table));
						$this->objData->$strField['file'] = $objUpload->uploadFile();
						
						// Inserts DB registry
						$arrData = array(
										'media_gallery_id'		=> null,
										'usr_data_id'			=> $this->intUserID,
										'type'					=> false,
										'name'					=> $this->objData->$strField['name'],
										'author'				=> $this->objData->$strField['author'],
										'label'					=> $this->objData->$strField['label'],
										'filepath'				=> $this->objData->$strField['file'],
										'filepath_thumbnail'	=> null,
										'filepath_streaming'	=> $this->objData->$strField['file']
									);
						$objInsert = $this->objModel->insert('media_data',$arrData,true);
						
						// Sets sys vars
						$this->objPrintData->$strField->id		= $objInsert;
						$this->objPrintData->$strField->file	= $this->objData->$strField['file'];
						$this->objPrintData->$strField->path	= $objUpload->strPath;
						$this->objPrintData->$strField->info	= $arrData;
					} else {
						$this->objData->$strField = $this->objPrintData->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
					}
				break;
				
				default:
					$this->objData->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
					$this->objPrintData->$strField = (is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField);
				break;
				
				/***********************************/
				/* NEEDS APPLR! SYSTEM DEFINITIONS */
				/***********************************/
				
				case 'multitext':
					if(is_array($this->objData->$strField)) {
						$this->objPrintData->$strField	= $this->objData->$strField;
						$this->objData->$strField		= implode("#MULTITEXT#",(is_array($mxdData) ? $mxdData[$strField] : $mxdData->$strField));
					} else {
						$this->objData->$strField		= null;
						$this->objPrintData->$strField	= '';
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
		#echo '<pre>'; print_r($this->objData);
		switch($intReturnMode) {
			case 0:
			default:
				return true;
			break;
			
			case 1:
				$objReturn 			= clone (object) $this->objData;
				$this->objData		= null;
				
				return $objReturn;
			break;
			
			case 2:
				$objReturn 			= clone (object) $this->objPrintData;
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
		
		if($this->setupFieldSufyx($mxdData)) {
			$this->intContentID = $this->objModel->insert($this->objSection->table,(array) $this->objData,true);
			return true;
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
		
		if($this->setupFieldSufyx($mxdData)) {
			if($this->objModel->update($this->objSection->table,(array) $this->objData,$this->objSection->table.'.id = ' . $intContentID) !== false) {
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
				if($this->setupRelField($mxdData,$objRel->field)) {
					foreach($this->arrRelData[$objRel->field] AS $intRelID) {
						// Inserts new rel data
						if($this->objModel->insert($objRel->table, ($objRel->parent ? array($objRelParams->strParentField => $this->intContentID, $objRelParams->strChildField => $intRelID) : array($objRelParams->strParentField => $intRelID, $objRelParams->strChildField => $this->intContentID)) )) {
							$this->arrRelContent[$objRel->field][] = $this->recordExists('name', ($objRel->parent ? $objRelParams->strParentTable : $objRelParams->strChildTable),'id = ' . $intRelID,true);
						} else {
							define('ERROR_MSG','$this->updateRelContent::insert error on ' . $mxdKey . '!');
							$this->arrRelContent[$objRel->field] = null;
						}
					}
				} else {
					define('ERROR_MSG','$this->setupRelField error on ' . $objRel->field . ' : ' . $objRel->table . '!');
					$this->arrRelContent[$objRel->field] = null;
				}
			} else {
				define('ERROR_MSG','$this->setRelParams error on ' . $mxdKey . '!');
				$this->arrRelContent[$objRel->field] = null;
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
		#echo '<pre>'; print_r($this->objRelField); die();
		foreach($this->objRelField AS $mxdKey => $objRel) {
			// Sets relationship params
			if($this->setRelParams($objRel)) {
				// Deletes previous related content
				if($this->objModel->delete($objRel->table,$objRelParams->strWhere) !== false) {
					// Setups rel fields
					if($this->setupRelField($mxdData,$objRel->field)) {
						foreach($this->arrRelData[$objRel->field] AS $intRelID) {
							// Inserts new rel data
							if($this->objModel->insert($objRel->table, ($objRel->parent ? array($objRelParams->strParentField => $this->intContentID, $objRelParams->strChildField => $intRelID) : array($objRelParams->strParentField => $intRelID, $objRelParams->strChildField => $this->intContentID)) ) !== false) {
								$this->arrRelContent[$objRel->field][] = $this->recordExists('name', ($objRel->parent ? $objRelParams->strParentTable : $objRelParams->strChildTable),'id = ' . $intRelID,true);
							} else {
								define('ERROR_MSG','$this->updateRelContent::insert error on ' . $mxdKey . '!');
								$this->arrRelContent[$objRel->field] = null;
							}
						}
					} else {
						define('ERROR_MSG','$this->setupRelField error on ' . $objRel->field . ' : ' . $objRel->table . '!');
						$this->arrRelContent[$objRel->field] = null;
					}
				} else {
					define('ERROR_MSG','$this->updateRelContent::delete error!');
					$this->arrRelContent[$objRel->field] = null;
				}
			} else {
				define('ERROR_MSG','$this->setRelParams error on ' . $mxdKey . '!');
				$this->arrRelContent[$objRel->field] = null;
				return false;
			}
		}
		
		return true;
	}
	
	private function setRelParams($objRel) {
		if(!is_object($objRel) || !isset($objRel->table) || !isset($objRel->parent)) 	return false;
		if(empty($objRel->table) || strpos($objRel->table,'rel_ctn_') !== 0) 			return false;
		if(empty($objRel->parent) || ($objRel->parent != 0 && $objRel->parent != 1)) 	return false;
		
		// rel_ctn_PARENT_NAME_ctn_CHILD_NAME
		$arrTmpTable	= explode('_ctn_',$objRel->table);
		if($objRel->parent) {
			$objRelParams->strParentTable 	= 'ctn_' . $arrTmpTable[1];
			$objRelParams->strParentField	= $objRelParams->strParentTable . '_id';
			$objRelParams->strChildTable	= 'ctn_' . $arrTmpTable[2];
			$objRelParams->strChildField	= $objRelParams->strChildTable . '_id';
			
			$objRelParams->strWhere			= $objRelParams->strParentField . ' = ' . $this->intContentID;
		} else {
			$objRelParams->strParentTable 	= 'ctn_' . $arrTmpTable[2];
			$objRelParams->strParentField	= $objRelParams->strParentTable . '_id';
			$objRelParams->strChildTable	= 'ctn_' . $arrTmpTable[1];
			$objRelParams->strChildField	= $objRelParams->strChildTable . '_id';
			
			$objRelParams->strWhere			= $objRelParams->strChildField . ' = ' . $this->intContentID;
		}
		return true;
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
}
?>