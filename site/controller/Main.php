<?php
class Main_controller extends manageContent_Controller {
	
	public $strPermalink;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct() {
		parent::__construct(0,false);

		// Sets Section vars
		$this->setSection(SECTION);
		$this->objSection	= $this->objModel->getSectionConfig(SECTION);
		
		$strWhere = str_replace('#table#',$this->objSection->table_name.'.',SYS_WHERE);
		
		// Sets Content List vars
		$this->objModel->strTable		= $this->objSection->table_name;
		$this->objModel->arrTable		= array($this->objSection->table_name);
		$this->objModel->arrFieldType	= $this->arrFieldType;
		
		$this->objModel->arrFieldList	= array('*');
		$this->objModel->arrJoinList	= array();
		$this->objModel->arrWhereList	= array($strWhere);
		$this->objModel->arrOrderList	= array('date_publish DESC');
		$this->objModel->arrGroupList	= array('id');
		
		$this->objModel->arrFieldData	= array($this->objModel->strTable . '.*');
		$this->objModel->arrJoinData	= array();
		$this->objModel->arrWhereData	= array($strWhere . ' AND ' . $this->objModel->strTable . '.id = {id}');
		$this->objModel->arrOrderData	= array($this->objModel->strTable . '.date_publish DESC');
		$this->objModel->arrGroupData	= array($this->objModel->strTable . '.id');
		
		if(!CONTENT) {
			$this->objData	= $this->content();
			$this->objData = $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData[0]),2,false);
			
			$strTPL = (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_list);
			
		} else {
			$this->objData = $this->objModel->getData($intID);
			
			foreach($this->arrRelContent AS $objRel) {
				if($objRel->type == 2) {
					$this->objData->{$objRel->field_name} = json_decode($this->objData->{$objRel->field_name});
				}
			}
			$this->objData = $this->setupFieldSufyx($this->objData,array_keys((array) $this->objData[0]),2);
			
			$strTPL = (HOME == 1 ? $this->objSection->tpl_home : $this->objSection->tpl_content);
		}

		$this->objSmarty->assign('objSection',$this->objSection);
		$this->objSmarty->assign('objData',$this->objData);
		
		if(!empty($strTPL)) $this->renderTemplate(true,$strTPL);
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
	 */
	public function update($intID = 0) {
		if(!is_numeric($intID) || $intID <= 0) {
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][5]);
		}
	
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->_read(); exit();
		}
	
		$intRel = 0;
		#echo '<pre>'; print_r($this->arrRelContent); die();
		foreach($this->arrRelContent AS $objRel) {
			$arrFields = explode('_rel_',str_replace(array('sec_rel_','_parent','_child'),'',$objRel->table_name));		// sec_rel_ mysec_parent mysec_child
			#echo '<pre>'; print_r($arrFields); die();
			$this->objModel->arrFieldData[]	= 'CONCAT("[",GROUP_CONCAT(DISTINCT CONCAT("{\"id\":\"",rel_ctn_0.id,"\",\"value\":\"",rel_ctn_0.' . $objRel->field_rel. ',"\"}") SEPARATOR ","),"]") AS ' . $objRel->field_name;
			$this->objModel->arrJoinData[]	= 'LEFT JOIN ' . $objRel->table_name . ' AS rel_tbl_' . $intRel . ' ON rel_tbl_' . $intRel . '.parent_id = ' . $this->objModel->strTable . '.id';
			$this->objModel->arrJoinData[]	= 'LEFT JOIN ' . $arrFields[1] . ' AS rel_ctn_' . $intRel . ' ON rel_tbl_' . $intRel . '.child_id = rel_ctn_' . $intRel . '.id';
			$intRel++;
		}
	
		$this->_create($intID);
	}
	
}