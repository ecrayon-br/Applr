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
	protected	$objRelParams;
	
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
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	public function setSection($intSection) {
		if(!is_numeric($intSection) || empty($intSection)) return false;
		
		$this->intSection = $intSection;
		
		return true;
	}
	
	/**
	 * Get SECTION fields data and sets $this->objFields
	 *
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	public function getSectionFields() {
		$this->objFields = $this->objModel->getSectionFields($this->intSection);
		
		if($this->objFields === false) return false; else return true;
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
		$this->objRelFields = $this->objModel->getRelSectionFields($this->intSection);
		
		if($this->objRelFields === false) return false; else return true;
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
							$strValue = HTTP_STATIC . self::permalinkSyntax($objSection->table) . '/' . $objSection->static_filename . '_' . $arrLink[1] . '.htm';
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
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 *
	 */
	private function setupFieldSufyx($mxdData) {
		if(!is_array($mxdData)) return false;
		if($this->objFields === false) return false;
		
		foreach($this->objFields AS $mxdKey => $strField) {
			$this->objData->$strField = (is_array($mxdData) ? (!empty($mxdData[$strField]) ? $mxdData[$strField] : '') : (!empty($mxdData->$strField) ? $mxdData->$strField : ''));
			
			$strTemp = end(explode('_',$strField));
			switch($strTemp) {
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
						
						$this->objPrintData->$strField->intval		= Controller::onlyNumbers($intPhone . $this->objData->$strField);
						$this->objPrintData->$strField->formatted	= (!empty($intPhone) ? '(' . $intPhone . ')' : '') . $this->objData->$strField;
						$this->objPrintData->$strField->original	= array($intPhone,$this->objData->$strField);
						$this->objData->$strField					= $this->objPrintData->$strField->intval;
					} else {
						$this->objPrintData->$strField = '';
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
				
						$this->objData->$strField 						= $intYear.'-'.$intMonth.'-'.$intDay;
						$this->objPrintData->$strField->formatted		= (LANGUAGE != 1 ? date('Y-m-d',strtotime($this->objData->$strField)) : date('d/m/Y',strtotime($this->objData->$strField)));
					} else {
						$this->objData->$strField						= '0000-00-00';
						$this->objPrintData->$strField->formatted		= (LANGUAGE != 1 ? '0000-00-00' : '00/00/0000');
					}
					$this->objPrintData->$strField->original->Day		= $intDay;
					$this->objPrintData->$strField->original->Year		= $intMonth;
					$this->objPrintData->$strField->original->Month		= $intYear;
				break;
				
				case 'hour':
					$intSecond	= $strField . '_Hour';
					$intSecond	= (is_array($mxdData) ? (!empty($mxdData[$intSecond]) 		? $mxdData[$intSecond]		: '00') 	: (!empty($mxdData->$intSecond) 	? $mxdData->$intSecond		: '00') );
					$intMinute	= $strField . '_Minute';
					$intMinute	= (is_array($mxdData) ? (!empty($mxdData[$intMinute])	? $mxdData[$intMinute]	: '00') 	: (!empty($mxdData->$intMinute) 	? $mxdData->$intMinute	: '00') );
					$intHour	= $strField . '_Second';
					$intHour	= (is_array($mxdData) ? (!empty($mxdData[$intHour])		? $mxdData[$intHour]	: '00') 	: (!empty($mxdData->$intHour) 	? $mxdData->$intHour	: '00') );
				
					if($intSecond > 0 || $intMinute > 0 || $intHour > 0) 	{
						$intSecond		= ($intSecond	> 0 ? $intSecond	: date('H'));
						$intMinute	= ($intMinute> 0 ? $intMinute	: date('i'));
						$intHour	= ($intHour	> 0 ? $intHour	: date('s'));
				
						$this->objData->$strField 						= $intHour.':'.$intMinute.':'.$intSecond;
						$this->objPrintData->$strField->formatted		= date('H:i:s',strtotime($this->objData->$strField));
					} else {
						$this->objData->$strField						= '00:00:00';
						$this->objPrintData->$strField->formatted		= '00:00:00';
					}
					$this->objPrintData->$strField->original->Hour		= $intHour;
					$this->objPrintData->$strField->original->Minute	= $intMinute;
					$this->objPrintData->$strField->original->Second	= $intSecond;
				break;
				
				case 'permalink':
					// Sets permalink string
					$strPermalink = Controller::permalinkSyntax($this->objData->$strField);
					$objData = $this->objModel->select('permalink',$this->objSection->table,array(),'permalink LIKE "' . $strPermalink . '%" AND sys_language_id = "' . LANGUAGE . '"');
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
				
				case 'multitext':
					if(is_array($this->objData->$strField)) {
						$this->objPrintData->$strField	= $this->objData->$strField;
						$this->objData->$strField		= implode("#MULTITEXT#",$mxdData[$strField]);
					} else {
						$this->objData->$strField		= null;
						$this->objPrintData->$strField	= '';
					}
				break;
				
				case 'upload':
					$objUpload = new uploadFile_Controller($strField,ROOT_UPLOAD . Controller::permalinkSyntax($this->objSection->table));
					$this->objData->$strField = $this->objPrintData->$strField = $objUpload->uploadFile();
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
					// Uploads file
					$objUpload = new uploadFile_Controller($strField,ROOT_IMAGE . Controller::permalinkSyntax($this->objSection->table));
					$this->objData->$strField['file'] = $objUpload->uploadFile();
					
					// Inserts DB registry
					$arrData = array(
									'media_gallery_id'		=> null,
									'usr_data_id'			=> $_SESSION[PROJECT]['id'],
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
				break;
				
				case 'video':
					// Uploads file
					$objUpload = new uploadFile_Controller($strField,ROOT_VIDEO . Controller::permalinkSyntax($this->objSection->table));
					$this->objData->$strField['file'] = $objUpload->uploadFile();
					
					// Inserts DB registry
					$arrData = array(
									'media_gallery_id'		=> null,
									'usr_data_id'			=> $_SESSION[PROJECT]['id'],
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
				break;
				
				default:
					$this->objPrintData->$strField = $mxdData;
				break;
				
				/***********************************/
				/* NEEDS APPLR! SYSTEM DEFINITIONS */
				/***********************************/
				
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
		
		return true;
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
			$this->intContent = $this->objModel->insert($this->objSection->table,(array) $this->objData,true);
			return true;
		} else {
			define('ERROR_MSG','$this->setupFieldSufyx error!');
			$this->intContent = null;
			return false;
		}
	}

	/**
	 * Updates main content and sets $this->contentID as last insert ID value
	 * 
	 * @param	integer	$intContent	Content's ID on DB
	 * @param	mixed	$mxdData	POST / GET / other data object
	 * 
	 * @return	boolean
	 *
	 * @since 	2013-01-22
	 * @author	Diego Flores <diego [at] gmail [dot] com>
	 * 
	 */
	protected function updateMainContent($intContent,$mxdData) {
		if(!is_array($mxdData)) return false;
		
		if($this->setupFieldSufyx($mxdData)) {
			if($this->objModel->update($this->objSection->table,(array) $this->objData,$this->objSection->table.'.id = ' . $intContent) !== false) {
				$this->intContent = $intContent;
				return true;
			} else {
				define('ERROR_MSG','$this->updateMainContent::update error!');
				$this->intContent = null;
				return false;
			}
		} else {
			define('ERROR_MSG','$this->setupFieldSufyx error!');
			$this->intContent = null;
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
		if($this->objRelFields === false) return false;
		
		foreach($this->objRelFields AS $mxdKey => $objRel) {
			// Sets relationship params
			if($this->setRelParams($objRel)) {
				// Setups rel fields
				if($this->setupRelField($mxdData,$objRel->field)) {
					foreach($this->arrRelData[$objRel->field] AS $intRelID) {
						// Inserts new rel data
						if($this->objModel->insert($objRel->table, ($objRel->parent ? array($objRelParams->strParentField => $this->intContent, $objRelParams->strChildField => $intRelID) : array($objRelParams->strParentField => $intRelID, $objRelParams->strChildField => $this->intContent)) )) {
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
		if($this->objRelFields === false) return false;
		
		foreach($this->objRelFields AS $mxdKey => $objRel) {
			// Sets relationship params
			if($this->setRelParams($objRel)) {
				// Deletes previous related content
				if($this->objModel->delete($objRel->table,$objRelParams->strWhere) !== false) {
					// Setups rel fields
					if($this->setupRelField($mxdData,$objRel->field)) {
						foreach($this->arrRelData[$objRel->field] AS $intRelID) {
							// Inserts new rel data
							if($this->objModel->insert($objRel->table, ($objRel->parent ? array($objRelParams->strParentField => $this->intContent, $objRelParams->strChildField => $intRelID) : array($objRelParams->strParentField => $intRelID, $objRelParams->strChildField => $this->intContent)) ) !== false) {
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
			
			$objRelParams->strWhere			= $objRelParams->strParentField . ' = ' . $this->intContent;
		} else {
			$objRelParams->strParentTable 	= 'ctn_' . $arrTmpTable[2];
			$objRelParams->strParentField	= $objRelParams->strParentTable . '_id';
			$objRelParams->strChildTable	= 'ctn_' . $arrTmpTable[1];
			$objRelParams->strChildField	= $objRelParams->strChildTable . '_id';
			
			$objRelParams->strWhere			= $objRelParams->strChildField . ' = ' . $this->intContent;
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