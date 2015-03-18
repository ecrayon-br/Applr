<?php
/**
 * Wrapper for .NET RegisteredTask Object
 * see: http://msdn.microsoft.com/en-us/library/aa382079%28VS.85%29.aspx
 * 
 * @author Alton Crossley <crossleyframework@nogahidebootstrap.com>
 * @package Crossley Framework
 *  
 * @copyright Copyright (c) 2003-2009, Nogaide BootStrap INC. All rights reserved.
 * @license BSD http://opensource.org/licenses/bsd-license.php
 * @version $Id:$
 * 
 */
class X_Scheduler_Ms_Task extends X_Ms_Variant
{
    const STATE_UNKNOWN = 0;
    const STATE_DISABLED = 1;
    const STATE_QUEUED = 2;
    const STATE_READY = 3;
    const STATE_RUNNING = 4;
    
    const ACTION_EXEC = 0;
    const ACTION_COM_HANDLER = 5;
    const ACTION_SEND_EMAIL = 6;
    const ACTION_SHOW_MESSAGE = 7;
    
    /**
     * The logon method is not specified. Used for non-NT credentials. 
     * 
     */
    const LOGON_NONE = 0;
    /**
     * Use a password for logging on the user. The password must be supplied at 
     * registration time.
     * 
     */
    const LOGON_PASSWORD = 1;
    /**
     * Use an existing interactive token to run a task. The user must log on 
     * using a service for user (S4U) logon. When an S4U logon is used, no 
     * password is stored by the system and there is no access to either the 
     * network or encrypted files.
     * 
     */
    const LOGON_S4U = 2;
    /**
     * User must already be logged on. The task will be run only in an existing 
     * interactive session.
     * 
     */
    const LOGON_INTERACTIVE_TOKEN = 3;
    /**
     * Group activation. The userId field specifies the group.
     * 
     */
    const LOGON_GROUP = 4;
    /**
     * Indicates that a Local System, Local Service, or Network Service account 
     * is being used as a security context to run the task.
     * 
     */
    const LOGON_SERVICE_ACCOUNT = 5;
    /**
     * First use the interactive token. If the user is not logged on (no 
     * interactive token is available), then the password is used. The password 
     * must be specified when a task is registered. This flag is not 
     * recommended for new tasks because it is less reliable than 
     * TASK_LOGON_PASSWORD.
     * 
     */
    const LOGON_INTERACTIVE_TOKEN_OR_PASSWORD = 6;
    
    Public Static $aDefaultSettings = array();
    
    Public $aStateNames = array(
        'unknown'
        ,'disabled'
        ,'queued'
        ,'ready'
        ,'running'
    );
    
	public $sName = 'Default';
	
	public $_oTriggers;
	
	public $_aTriggersArray = array();
	
	public $sTemplate = 'task.tpl.html';
	
	function __construct(X_Scheduler_Ms_Service $oScheduler, VARIANT $vTask = null)
	{
	    if ($vTask == null)
	    {
	        $oVariant = $oScheduler->getNewTaskVariant();
	        parent::__construct($oVariant);	        
	    }
	    else
	    {
	        parent::__construct($vTask);
	    }
	    	    
	}
	
	public function getTriggers()
	{
		if (empty($this->_oTriggers)) 
		{
		    if (!isset($this->_oVariant)) throw new X_Scheduler_Exception('Task Variant not set ' . X_Debug::out($this) . "<hr>");
			$this->_oTriggers = $this->_oVariant->Triggers;
		}
		
		return $this->_oTriggers;	
	}
	
	public function setRegistrationInfo($sAuthor, $sDescription = '')
	{
	    if (!isset($this->_oVariant))
	    {
	        return;
	    }
	    $oInfo = $this->_oVariant->RegistrationInfo;
	    $oInfo->Author = $sAuthor;
	    $oInfo->Description = $sDescription;
	}
	
	public function getTriggersArray()
	{
		if (empty($this->_aTriggersArray))
		{
			$oTriggersCollection = $this->getTriggers();
			foreach ($oTriggersCollection as $oTrigger)
			{
				$this->_aTriggersArray[] = $oTrigger;
			}
		}
		
		return $this->_aTriggersArray;
	}
	
	public function createTriggerVariant($iTriggerType)
	{
		return $this->getTriggers()->Create((int)$iTriggerType);
	}
	/**
	 * create a new trigger for this task
	 * @param X_Scheduler_Ms_Trigger $iTriggerType
	 */
	public function newTrigger($iTriggerType)
	{
		return new X_Scheduler_Ms_Trigger($this->createTriggerVariant($iTriggerType));
	}
	/**
	 * wrapper for magic _toArray()
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_toArray();
	}
	
	public function getStateName()
	{
	    Return (array_key_exists($this->_oVariant->State ,$this->aStateNames)) ? 
	        $this->aStateNames[$this->_oVariant->State] :
	        'unknown';	    
	}
	
	public function setLoginType($iLoginType = 3)
	{
	        $this->_oVariant->Principal->LogonType = $iLoginType;
	    //->Principal->LogonType = $iLoginType;
	}
	
    /**
     * simple array conversion method
     *
     * @return array
     */
    public function _toArray()
    {
        $aReturn = array('xml' => $this->_oVariant->XML);
        
        $aReturn['name'] = $this->_oVariant->Name;
        $aReturn['state'] = $this->getStateName();
        $aReturn['state_id'] = $this->_oVariant->State;
        $aReturn['path'] = $this->_oVariant->Path;
        $aReturn['missed_runs'] = $this->_oVariant->NumberOfMissedRuns;
        $aReturn['next_run_time'] = variant_date_to_timestamp($this->_oVariant->NextRunTime);
        $aReturn['next_run_time_text'] = date('m/d/y g:i:s a', $aReturn['next_run_time']);
        $aReturn['last_task_result'] = $this->_oVariant->LastTaskResult;
        $aReturn['last_run_time'] = variant_date_to_timestamp($this->_oVariant->LastRunTime);
        $aReturn['last_run_time_text'] = date('m/d/y g:i:s a', $aReturn['last_run_time']);
        $aReturn['enabled'] = $this->_oVariant->Enabled;
        $aReturn['actions'] = $this->_oVariant->Definition->Actions;
        $aReturn['principal'] = $this->_oVariant->Definition->Principal->DisplayName;
        
        // SETTINGS -----------------------------------------------------------------------------------
        $aReturn['allow_demand_start'] = $this->_oVariant->Definition->Settings->AllowDemandStart;
        $aReturn['allow_hard_terminate'] = $this->_oVariant->Definition->Settings->AllowHardTerminate;
        $aReturn['compatibility'] = $this->_oVariant->Definition->Settings->Compatibility;
        $aReturn['delete_expired_task_after'] = $this->_oVariant->Definition->Settings->DeleteExpiredTaskAfter;
        $aReturn['disallow_start_if_on_batteries'] = $this->_oVariant->Definition->Settings->DisallowStartIfOnBatteries;
        $aReturn['enabled'] = $this->_oVariant->Definition->Settings->Enabled;
        $aReturn['execution_time_limit'] = $this->_oVariant->Definition->Settings->ExecutionTimeLimit;
        $aReturn['bidden'] = $this->_oVariant->Definition->Settings->Hidden;
        // see http://msdn.microsoft.com/en-us/library/aa380669%28VS.85%29.aspx
        // $aReturn['IdleSettings'] = $this->_oVariant->Definition->Settings->IdleSettings;
        
        // see http://msdn.microsoft.com/en-us/library/aa383507%28VS.85%29.aspx
        $aReturn['multiple_nstances'] = $this->_oVariant->Definition->Settings->MultipleInstances;
        
        $aReturn['profile_id'] = $this->_oVariant->Definition->Settings->NetworkSettings->Id;
        $aReturn['profile_name'] = $this->_oVariant->Definition->Settings->NetworkSettings->Name;
        
        // 1-10 one being highest priority
        $aReturn['priority'] = $this->_oVariant->Definition->Settings->Priority;
        
        $aReturn['restart_count'] = $this->_oVariant->Definition->Settings->RestartCount;
        $aReturn['restart_interval'] = $this->_oVariant->Definition->Settings->RestartInterval;
        
        $aReturn['run_only_if_idle'] = $this->_oVariant->Definition->Settings->RunOnlyIfIdle;
        $aReturn['run_only_if_network_available'] = $this->_oVariant->Definition->Settings->RunOnlyIfNetworkAvailable;
        $aReturn['start_when_available'] = $this->_oVariant->Definition->Settings->StartWhenAvailable;
        $aReturn['stop_if_going_on_batteries'] = $this->_oVariant->Definition->Settings->StopIfGoingOnBatteries;
        $aReturn['wake_to_run'] = $this->_oVariant->Definition->Settings->WakeToRun;
                
        return $aReturn;
    }
    
    public function setSettings(Array $aSettings = array())
    {
        $aNewSettings = self::$aDefaultSettings + $aSettings;
        
        foreach ($aNewSettings as $sKey => $xValue) {
        	$this->_oVariant->Settings->$sKey = $xValue;
        }
    }
    /**
     * create an action for this task
     * Note: only supports execution actions at the moment
     * 
     * @param string $sAction
     * @param int $iActionType ActionType compatible integer
     */
    public function addExecAction($sAction, $iActionType, $sArgs = null, $sDir = null)
    {
        $vAction = $this->_oVariant->Actions->Create((int)$iActionType);
        $vAction->Path = $sAction;
        
        if(!empty($sArgs)) $vAction->Arguments = $sArgs;
        if(!empty($sDir)) $vAction->WorkingDirectory = $sDir;
        /**
         * also
         * $vAction->Arguments
         * $vAction->WorkingDirectory
         */
    } 
    
    /**
     * return debug array
     *
     * @return array
     */
    public function debug()
    {
    	return $this->_toArray();
    }
    
    public function __toString()
    {
    	return X_Array_Tokenizer::combine($this->toArray(), $this->sTemplate);
    }
    
    /**
     * immediately run the task
     */
    public function run()
    {
        $vEmpty = X_Ms_Variant::none();
        $this->_oVariant->Run($vEmpty);
    }
    
    public function isRunning()
    {
        return ($this->_oVariant->State == self::STATE_RUNNING);
    }
    /**
     * immediately stop the current task
     */   
    public function stop()
    {
        $vEmpty = X_Ms_Variant::none();
        $this->_oVariant->Stop($vEmpty);
    }
    /**
     * get the path to where the registered task is stored
     * @return string
     */
    public function getPath()
    {
        return $this->_oVariant->Path;
    }
    /**
     * get the xml-formatted registration information for the registered task
     * @return string
     */
    public function getXML()
    {
        return $this->_oVariant->XML;
    }
}
