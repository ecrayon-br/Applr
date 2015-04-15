<?php
class SectionContent_controller extends manageContent_Controller {
	
	private		$strSecTable;
	private		$arrSectionActions = array('update','insert','add','list','duplicate','delete');
	private		$strSectionAction;

	protected	$arrFieldType	= array();
	
	public		$objFCK;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 * @param	integer	$intSecID	Defines SECTION ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	
	public function __construct($boolRenderTemplate = true,$intSecID = 0) {
		parent::__construct($intSecID,true,CMS_ROOT_TEMPLATE);
		
		// Gets Section ID from Section Permalink
		/**
		 * @todo set PROJECT_ID
		 * @todo review if/else block below
		 */
		if(!empty($intSecID)) {
			$this->intSecID	= intval($intSecID);
			$strSection		= $this->objModel->recordExists('permalink','sec_config','id = "' . $this->intSecID . '"',true);
		} else {
			$strSection		= (!in_array($_SESSION[self::$strProjectName]['URI_SEGMENT'][3],$this->arrSectionActions) ? $_SESSION[self::$strProjectName]['URI_SEGMENT'][3] : $_SESSION[self::$strProjectName]['URI_SEGMENT'][4]);
			$this->intSecID	= intval($this->objModel->recordExists('id','sec_config','permalink = "' . $strSection . '"',true));
			
			if(empty($this->intSecID)) {
				$this->intSecID	= intval($this->objModel->recordExists('id','sec_config','id = "' . $strSection . '"',true));
			}
			if(empty($_SESSION[self::$strProjectName]['URI_SEGMENT'][3]) || empty($this->intSecID)) {
				$this->objSmarty->assign('ALERT_MSG','There was an error retrieving Section\'s data!');
			}
		}
		$this->objSmarty->assign('strPermalink',$strSection);
		
		// Sets SECTION ACTION
		$this->strSectionAction = (in_array($_SESSION[self::$strProjectName]['URI_SEGMENT'][3],$this->arrSectionActions) ? $_SESSION[self::$strProjectName]['URI_SEGMENT'][3] : (in_array($_SESSION[self::$strProjectName]['URI_SEGMENT'][4],$this->arrSectionActions) ? $_SESSION[self::$strProjectName]['URI_SEGMENT'][4] : 'list') );
		$this->objSmarty->assign('strSectionAction',$this->strSectionAction);
		
		// Sets Section vars
		$this->setSection($this->intSecID);
		
		// Gets Section Fields list
		$this->getFieldList();
		
		/**
		 * @todo Checar a setagem direta de parametros ->objModel; o correto não seria setar as do controller???
		 */
		// Sets Content List vars
		$this->objModel->strTable		= $this->objSection->table_name;
		$this->objModel->arrTable		= array($this->objSection->table_name);
		$this->objModel->arrFieldType	= $this->arrFieldType;
		
		$this->objModel->arrFieldList	= array($this->objSection->table_name . '.*');
		$this->objModel->arrJoinList	= array();
		$this->objModel->arrWhereList	= array($this->objSection->table_name . '.deleted = 0');
		$this->objModel->arrOrderList	= array($this->objSection->orderby);
		$this->objModel->arrGroupList	= array($this->objSection->table_name . '.id');
		$this->objModel->intOffsetList	= 0; #($this->objSection->list_items ? ($this->intCurrentPage * $this->objSection->list_items) - $this->objSection->list_items : null);
		$this->objModel->intLimitList	= null; #intval($this->objSection->list_items);
		
		$this->objModel->arrFieldData	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinData	= array();
		$this->objModel->arrWhereData	= array($this->objModel->strTable . '.id = {id}');
		$this->objModel->arrOrderData	= array($this->objSection->orderby);
		$this->objModel->arrGroupData	= array($this->objModel->strTable . '.id');

		// Sets related content sql instructions
		$this->setRelContent();
		
		// Shows default interface
		if($boolRenderTemplate) $this->_read();
	}
	
	public function duplicate() {
		// Gets parent content
		$this->update(0,false);
		
		// Setups data object
		$this->objRawData->id 	= null;
		$this->objRawData->name = 'Copy ' . date('YmdHis - ') . $this->objRawData->name; 
		foreach($this->objPrintData AS $strKey => $mxdData) {
			if(is_array($mxdData)) {
				if($this->arrRelContent[$strKey]->type == 2) {
					$this->objRawData->$strKey = reset($this->objRawData->$strKey)->id;
				} else {
					foreach($this->objRawData->$strKey AS &$objData) {
						$objData = $objData->id;
					}
				}
			}
		}
		#echo '<pre>'; print_r($this->objRawData); die();
		$this->add($this->objRawData);
	}
	
	/**
	 * Shows INSERT / UPDATE form interface
	 *
	 * @param	integer	$intID			Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function _create($intID = 0, $boolRenderTemplate = true) {
		if($intID > 0) {
			$this->objRawData = $this->objModel->getData($intID);
			
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objRawData->{$objRel->field_name} = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $this->objRawData->{$objRel->field_name}));
					$this->objPrintData->{$objRel->field_name} = $this->objRawData->{$objRel->field_name};
				}
			}
			
			if(empty($this->objPrintData->id)) $this->objPrintData = $this->setupFieldSufyx($this->objRawData,array_keys((array) $this->objRawData),2);
						
			$this->objSmarty->assign('objData',$this->objPrintData);
			
		}
		
		if($boolRenderTemplate) $this->renderTemplate(true,$this->strModule . '_form.html');
	}
	
	/**
	 * Shows INSERT interface
	 * 
	 * @param	integer	$intID	Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function insert() {
		$this->_create();
	}
	
	/**
	 * Shows UPDATE interface
	 * 
	 * @param	integer	$intID	Content ID
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 * 
	 * @todo Check $this->objRelField and $this->objRelContent CONCAT/GROUP_CONCAT
	 *
	 */
	public function update($intID = 0,$boolRenderTemplate = true) {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]); 
		}
		
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read(); exit();
		}
		
		$this->_create($intID,$boolRenderTemplate);
	}
	
	/**
	 * @see CRUD_Controller::delete()
	 */
	public function delete() {
		$this->strTable	= $this->objSection->table_name;
		$this->arrTable	= array($this->objSection->table_name);
		
		parent::delete(1);
	}
	
	/**
	 * Inserts / Updates data
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function add($objData = null) {
		$this->unsecureGlobals();
		
		// Sets Section's DB Entity name
		#$strTable		= $this->strTable;
		#$arrTable		= $this->arrTable;
		$this->strTable	= $this->objSection->table_name;
		$this->arrTable	= array($this->objSection->table_name);
		
		// Sets Section's default fields
		$this->arrFieldType['id']				= 'integer_empty';
		$this->arrFieldType['lang_content']		= 'integer';
		$this->arrFieldType['usr_data_id']		= 'integer_empty';
		$this->arrFieldType['sys_language_id']	= 'integer';
		$this->arrFieldType['date_create']		= 'date';
		$this->arrFieldType['create_html']		= 'boolean';
		$this->arrFieldType['deleted']			= 'boolean';
		$this->arrFieldType['permalink']		= 'string';
		$this->arrFieldType['date_publish']		= 'date';
		$this->arrFieldType['date_expire']		= 'date_empty';
		$this->arrFieldType['active']			= 'boolean';
		$this->arrFieldType['seo_description']	= 'string_empty';
		$this->arrFieldType['seo_keywords']		= 'string_empty';
		
		$objData								= (is_null($objData) || !is_object($objData) ? (object) $_POST : $objData);
		
		$objData->lang_content					= 0;
		$objData->date_create					= date('YmdHis');
		
		// If INSERT, defines PERMALINK; if UPDATE, keeps previous content
		if(empty($objData->id)) {
			$objData->permalink	= $this->permalinkSyntax($objData->name);
		} else {
			$objData->permalink = $this->objModel->recordExists('permalink',$this->strTable,'id = "' . $objData->id . '"',true);
		}
		
		if(empty($objData->sys_language_id))	$objData->sys_language_id	= $objData->sys_language_id	= LANGUAGE;
		if(empty($objData->date_publish))	{
			$objData->date_publish	= date('YmdHis');
		} else {
			$objData->date_publish	.= ' ' . $objData->time_publish;
		}
		if(empty($objData->date_expire))	{
			$objData->date_expire		= '00000000000000';
		} else {
			$objData->date_expire		.= ' ' . $objData->time_expire;
		}

		$this->objData	= $this->setupFieldSufyx($objData,array_keys((array) $objData),1);
		
		// Insert / Updates data
		$objReturn = $this->_update((array) $this->objData,false);
		
		// If is DUPLICATE and $objReturns == false, shows content's list
		if(!$objReturn && $this->strSectionAction == 'duplicate') {
			$this->_read();
			exit();
		} else {
	
			// Formats data array to screen
			$this->objPrintData	= reset($this->objPrintData);
			
			// If id duplicating content, sets new ID
			if($this->strSectionAction == 'duplicate') { $this->objPrintData->id = $this->objData->id; }
			
			// Shows Section's form template
			if(empty($this->objPrintData->id)) {
				$this->insert();
			} else {
				$this->update($this->objPrintData->id);
			}
			
			$this->secureGlobals();
			
		}
	}
}