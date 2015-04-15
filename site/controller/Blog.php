<?php
class Blog_controller extends Main_controller {
	
	protected $intSecID = 10;
	protected $isLead;
	
	/**
	 * Class constructor
	 *
	 * @return	void
	 *
	 * @since 	2013-02-07
	 * @author 	Diego Flores <diegotf [at] gmail [dot] com>
	 *
	 */
	public function __construct($boolRenderTemplate = true) {
		// Clears control cookies
		if(!empty($_REQUEST['clear'])) {
			setcookie(PROJECT . '_isLead',true,1,'/');
			setcookie(PROJECT . '_confirmLead',true,1,'/');
		}
		
		parent::__construct(false,$this->intSecID);
		
		// Checks if CONFIRM LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_confirmLead'])) {
			$this->confirmLead = false;
		} else {
			$this->confirmLead = true;
		}
		$this->objSmarty->assign('confirmLead',$this->confirmLead);
		
		// Checks if IS LEAD cookie exists
		if(!isset($_COOKIE[PROJECT . '_isLead'])) {
			$this->isLead = false;
		} else {
			$this->isLead = true;
		}
		$this->objSmarty->assign('isLead',$this->isLead);

		// Sets Blog's HASHTAGS
		$this->setHashtags();
		
		// Gets Blog's Channel relationship
		$objChannel = $this->objModel->select(array('aet_fl_channel.id','aet_fl_channel.name','aet_fl_channel.permalink'),'aet_fl_channel',array('JOIN sec_rel_aet_fl_blog_rel_aet_fl_channel AS sec_rel'),array('sec_rel.child_id = aet_fl_channel.id'),array('aet_fl_channel.name ASC'),'aet_fl_channel.id');
		$this->objSmarty->assign('objChannel',$objChannel);
		
		// If content is defined, checks for PRIVATE param
		if($this->intContentID && !$this->isLead && $this->objData->private_bool) {
			
			if($boolRenderTemplate) {
				// Renders template
				$this->renderTemplate();
				
			} elseif(strpos($_SERVER["HTTP_REFERER"],DIR_HOMOLOG . '/blog') === false) {
				// Gets related HOTSPOT
				$strHotspot 	= $this->objModel->recordExists('permalink', 'aet_fl_destino AS hotspot JOIN sec_rel_aet_fl_destino_rel_aet_fl_blog AS rel','hotspot.id = rel.parent_id AND rel.child_id = ' . $this->intContentID,true);
				header("Location: " . HTTP . "hotspot/" . $strHotspot);
				exit();
			}
			
		} elseif($boolRenderTemplate) {
			// Renders template
			$this->renderTemplate();
		}
	}
	
	protected function setHashtags($strField = 'hashtag') {
		if(in_array($strField,$this->objField)) {
			$objData = (!$this->intContentID ? $this->objData : $this->objModel->select($strField,$this->objSection->table_name,array(),$this->strWhere) );
			
			$arrHashtag = array();
			foreach($objData AS $objTemp) {
				$arrTemp = explode(',',str_replace(array(' , ',', ',' ,'),',',strtolower($objTemp->$strField)));
				$arrHashtag = array_merge($arrHashtag,array_diff($arrTemp,$arrHashtag));
			}
			
			$this->objSmarty->assign('arrHashtag',$arrHashtag);
		} else {
			$arrHashtag = array();
			$this->objSmarty->assign('arrHashtag',$arrHashtag);
		}
	}
}
?>
