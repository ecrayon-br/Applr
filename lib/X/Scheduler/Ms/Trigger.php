<?php
/**
 * 
 * @todo description of this class
 * 
 * @author Alton Crossley <crossleyframework@nogahidebootstrap.com>
 * @package Crossley Framework
 *  
 * @copyright Copyright (c) 2003-2009, Nogaide BootStrap INC. All rights reserved.
 * @license BSD http://opensource.org/licenses/bsd-license.php
 * @version $Id:$
 * 
 */
class X_Scheduler_Ms_Trigger extends X_Ms_Variant
{
	const TRIGGER_EVENT = 0;
	const TRIGGER_TIME = 1;
	const TRIGGER_DAILY = 2;
	const TRIGGER_WEEKLY = 3;
	const TRIGGER_MONTHLY = 4;
	const TRIGGER_MONTHLYDOW = 5;
	const TRIGGER_IDLE = 6;
	const TRIGGER_REGISTRATION = 7;
	const TRIGGER_BOOT = 8;
	const TRIGGER_LOGON = 9;
	const TRIGGER_SESSION_STATE_CHANGE = 11;
	/**
	 * time limit string of five minutes
	 * @var string
	 */
	const LIMIT_5MINUTES = "PT5M";
	
	/**
     * Com factory wraper
     * Server informaiton can be passed using an array like:
     * array(
     *		'Server' => string
     * 		'Username' => string
     * 		'Password' => string
     * 		'Flags' => int (see PHP COM manual)
     * )
     *
     * @param string|array $xServerName
     * @param string $Codepage
     * @param int $TypeLib
     */
    function __construct(VARIANT $oVariant = null)
    {
        parent::__construct($oVariant);
        $this->_oVariant->Id = "RegistrationTriggerId";
    }
	
	/**
	 * ge ExecutionTimeLimit
	 * @return string
	 */
    public function getTimeLimit()
    {
    	return $this->_oVariant->ExecutionTimeLimit;
    }
    /**
     * set ExecutionTimeLimit
     * 
     * @param string $sTimeLimit
     */
    public function setTimeLimit($sTimeLimit)
    {
    	$this->_oVariant->ExecutionTimeLimit = $sTimeLimit;
    }
    
    public function enable($bEnabled = true)
    {
        $this->_oVariant->Enabled = $bEnabled;
    }
    
    public function isEnabled()
    {
        return $this->_oVariant->Enabled;
    }
}