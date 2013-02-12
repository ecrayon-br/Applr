<?php
class CRUD_Controller extends Main_controller {
	protected 	$objModel;
	protected	$objModelCRUD;

	protected	$strModule		= '';
	protected	$strTable		= '';
	protected	$arrTable		= array();
	protected	$arrFieldType	= array();
	protected	$arrFieldList	= array();
	protected	$arrJoinList	= array();
	protected	$arrWhereList	= array();
	protected	$arrOrderList	= array();
	protected	$arrGroupList	= array();
	protected	$arrFieldData	= array();
	protected	$arrJoinData	= array();
	protected	$arrWhereData	= array();
	protected	$arrOrderData	= array();
	protected	$arrGroupData	= array();
	
	public		$objData;
	
	/**
	 * Class constructor
	 *
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		parent::__construct(false);
		
		if(!DEBUG) authUser_Controller::isLoggedIn(true,'Login.html');
		
		$this->objModelCRUD	= new Crud_Model($this->arrTable,$this->arrFieldType,$this->arrFieldList,$this->arrJoinList,$this->arrWhereList,$this->arrOrderList,$this->arrGroupList,$this->arrFieldData,$this->arrJoinData,$this->arrWhereData,$this->arrOrderData,$this->arrGroupData);
		
		$this->strModule 	= str_replace(array('_Controller','_controller'),'',get_class($this));
		$strModelClass		= $this->strModule . '_model';
		
		if(class_exists($strModelClass)) {
			$this->objModel = new $strModelClass($this->arrTable,$this->arrFieldType,$this->arrFieldList,$this->arrJoinList,$this->arrWhereList,$this->arrOrderList,$this->arrGroupList,$this->arrFieldData,$this->arrJoinData,$this->arrWhereData,$this->arrOrderData,$this->arrGroupData);
		} else {
			$this->objModel = $this->objModelCRUD;
		}
		
		if($boolRenderTemplate) $this->_read();
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
	 */
	public function update($intID = 0) {
		if(!is_numeric($intID) || $intID <= 0) { 
			$intID = intval($_SESSION[self::$strProjectName]['URI_SEGMENT'][4]); 
		}
		if(!is_numeric($intID) || $intID <= 0) {
			$this->objSmarty->assign('ALERT_MSG','You must choose an item to update!');
			$this->renderTemplate(); exit();
		}
		
		$this->_create($intID);
	}
	
	/**
	 * Deletes data
	 * 
	 * @param	boolean	$boolMode	Deletion mode: 0 => DELETE; 1 => UPDATE deleted ATTR
	 *
	 * @return	void
	 *
	 * @since 	2013-02-08
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function delete($boolMode = 1) {
		if(!isset($_POST['delete']) || empty($_POST['delete'])) {
			$this->objSmarty->assign('ERROR_MSG','You must check items to delete!');
		} else {
			$objReturn = $this->_delete($_POST['delete'],$boolMode);
			if($objReturn !== false) {
				$this->objSmarty->assign('ALERT_MSG',str_replace('#',$objReturn,'# item(s) deleted successfully!'));
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error trying to delete data! Please try again!');
			}
		}
		
		// Shows list interface
		$this->_read();
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
	protected function _create($intID = 0) {
		if($intID > 0) {
			$this->objData = $this->objModelCRUD->getData($intID);
			$this->objSmarty->assign('objData',$this->objData);
		}
		
		$this->renderTemplate(true,$this->strModule . '_form.html');
	}
	
	/**
	 * Lists content
	 *
	 * @return	void
	 *
	 * @since 	2013-02-09
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _read() {
		$this->objData	= $this->objModelCRUD->getList();
		$this->objSmarty->assign('objData',$this->objData);
		$this->renderTemplate();
	}
	
	/**
	 * Inserts / Updates data
	 * 
	 * @param	array	$arrData	Array field => value paired to updates
	 * @param	boolean	$boolRenderTemplate	Defines whether to show default's interface
	 *
	 * @return	void
	 *
	 * @since 	2013-02-09
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _update($arrData,$boolRenderTemplate = true) {
		if(!is_bool($boolRenderView) && $boolRenderView !== 1 && $boolRenderView !== 0)	$boolRenderView = true;
		
		if(isset($this->arrFieldType['usr_data_id'])) $arrData['usr_data_id'] = $this->intUserID;
		
		if(($strField = $this->validateParamsArray($arrData,$this->arrFieldType,false)) === true) {
			if(($intID = $this->objModelCRUD->replace($this->strTable,$arrData)) !== false) {
				if(empty($arrData['id'])) {
					$arrData['id']	= $intID;
				} else {
					$intID = $arrData['id'];
				}
				$this->objSmarty->assign('ALERT_MSG','Data saved successfully!');
			} else {
				$this->objSmarty->assign('ERROR_MSG','There was an error while trying to save data! Please try again!');
				return false;
			}
		} else {
			$this->objSmarty->assign('ERROR_MSG','There was an error while validating "' . $strField . '" data! Please try again!');
			return false;
		}
		
		$this->objData	= (object) array_merge((array) $this->objData,$arrData);
		
		if($boolRenderTemplate) {
			$this->objSmarty->assign('objData',$this->objData);
			$this->_create($intID);
		}
		
		return true;
	}
	
	/**
	 * Deletes DB data
	 * 
	 * @param 	array	$arrWhere	WHERE conditions
	 * @param	boolean	$boolMode	Deletion mode: 0 => DELETE; 1 => UPDATE deleted ATTR
	 *
	 * @return	boolean
	 *
	 * @since 	2013-02-09
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	protected function _delete($arrWhere,$boolMode = 1) {
		if(!is_array($arrWhere)) 						$arrWhere = array($arrWhere);
		if(!is_bool($boolMode) && !is_numeric($boolMode) || (is_numeric($boolMode) && $boolMode != 0 && $boolMode != 1)) return false;
		
		if($boolMode) {
			return $this->objModelCRUD->update($this->strTable,array('deleted' => 1),'id IN (' . implode($arrWhere) . ')');
		} else {
			return $this->objModelCRUD->delete($this->strTable,'id IN (' . implode(',',$arrWhere) . ')');
		}
	}
	
}