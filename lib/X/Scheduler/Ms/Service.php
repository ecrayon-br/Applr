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
class X_Scheduler_Ms_Service extends X_Ms_Com_Abstract
{
    /**
     * signifies tasks that are hidden
     *
     */
    const VISIBILITY_ALL = 1;
    /**
     * signifies tasks that are not hidden
     *
     */
    const VISIBILITY_NORMAL = 0;
    
    const VALIDATE_ONLY = 1;
    const CREATE = 2;
    const UPDATE = 4;
    const CREATE_OR_UPDATE = 6;
    const DISABLE = 8;
    const DONT_ADD_PRINCIPAL_ACE = 10;
    const IGNORE_REGISTRATION_TRIGGERS = 20;
    
    /**
     * comand line executable string
     * 
     * @var string
     */
    public $sCommand;
    
    /**
     * default task folder
     *
     * @var string
     */
    public $sTaskFolder = '\\';
    /**
     * name of COM Module
     *
     * @var string
     */
    protected $_sModuleName = "Schedule.Service";

    /**
     * instance of selected folder
     *
     * @var Variant
     */
    protected $_oFolder;
    /**
     * instnace of current working task
     *
     * @var X_Scheduler_Ms_Task
     */
    protected $_oTask;
    
    /**
     * ms variant collection of tasks
     *
     * @var unknown_type
     */
    protected $_oTaskCollection;
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
    function __construct($xServer = null, $Codepage = null, $TypeLib = null, $sFolder = null)
    {
        // 1. Create a TaskService object. 
        // This object allows you to create the task in a specified folder.
        parent::__construct($xServer, $Codepage, $TypeLib);
        $this->_oVariant->Connect();
        $this->getFolder($sFolder);
    }
    /**
     * connect to the running task scheduler process
     *
     * @param string $sUsername
     * @param string $sPassword
     * @param string $sServer
     * @param string $sDomain
     */
    function connect($sUsername, $sPassword = null, $sServer = null, $sDomain = null)
    {
        $this->_oVariant->Connect($sServer, $sUsername, $sDomain, $sPassword);
    }
    
    public function deleteTask($sTaskName) {
    	if(empty($sTaskName)) return false;
    	
    	$objList = $this->getTasks(0);
    	foreach($objList AS $objTask) {
    		if($objTask->_oVariant->Name == $sTaskName) {
    			$this->_oFolder->DeleteTask($objTask->_oVariant->Name,0);
    		}
    	}
    	return true;
    }
    
    /**
     * select the folder in which to list/creat tasks
     *
     * @param string $sFolderPath
     * @return variant
     */
    public function getFolder($sFolderPath = null)
    {
        if ($sFolderPath)
        {
        	#print X_Debug::out('CHAR: '.substr($sFolderPath, -1));
        	// remove trailing slash
        	if (substr($sFolderPath, -1) == '\\')
        	{
        		$sFolderPath = substr($sFolderPath, 0, -1);
        	}
        	
        	try 
        	{
                $this->sTaskFolder = $sFolderPath;
                $this->_oFolder = $this->_oVariant->GetFolder($this->sTaskFolder);
                return $this->_oFolder;
        	}
        	catch (Exception $oException)
        	{
        		$this->sTaskFolder = null;
        		throw new X_Scheduler_Exception('unable to set folder to '.$sFolderPath);
        	}
        }
        
        if (isset($this->_oFolder))
        {
        	return $this->_oFolder;
        }
        $this->_oFolder = $this->_oVariant->GetFolder($this->sTaskFolder);
        
        return $this->_oFolder;
    }
    /**
     * set the folder path
     *
     * @param string $sFolderPath
     * @return variant
     */
    public function setFolder($sFolderPath)
    {
    	return $this->getFolder($sFolderPath);
    }
    /**
     * create a new task
     *
     * @return X_Scheduler_Ms_Task
     */
    public function newTask()
    {
        // get a new task, and save a refrence to it
    	$this->_oTask = new X_Scheduler_Ms_Task($this);
    	return $this->_oTask;
    }
    
    public function getNewTaskVariant()
    {
        // _oVariant->NewTask(0) - 0 is passed because a param is required but not implemented
        return $this->_oVariant->NewTask(0);
    }
    
    /**
     * schedule a command
     * currently only supports time triggers
     * @todo add other trigger support
     * 
     * @param string $sCommand
     * @param int $xTrigger
     * @param int $cTriggerType
     */
    public function scheduleCommand($sCommand, $xTrigger, $cTriggerType = 1, $xTriggerEnd=3600, $sArgs = null)
    {
        $this->sCommand = $sCommand;
        // 1. (see constructor) 
        
    	// 2. Get a task folder (see constructor) and create a task. 
    	// Use the TaskService.GetFolder method to get the folder where the 
    	// task is stored and the TaskService.NewTask method to create the 
    	// TaskDefinition object that represents the task.
        $oTask = $this->newTask();
        
        // 3. Define information about the task using the TaskDefinition object.
        // Use the TaskDefinition.Settings property to define the settings that
        // determine how the Task Scheduler service performs the task and the 
        // TaskDefinition.RegistrationInfo property to define the information 
        // that describes the task.
    	// (see X_Scheduler_Ms_Task::__constructor())
    	
    	// @todo get the current windows authenticated user and use that name
    	$this->_oTask->setRegistrationInfo("Dante_Job_Scheduler", "executing " . $sCommand);
    	$this->_oTask->setLoginType(X_Scheduler_Ms_Task::LOGON_INTERACTIVE_TOKEN);
    	   	
    	
    	// 4. Create a time-based trigger using the TaskDefinition.Triggers 
    	// property. This property provides access to the TriggerCollection 
    	// object. Use the TriggerCollection.Create method (specifying the type 
    	// of trigger you want to create) to create a time-based trigger. As 
    	// you create the trigger, set the start boundary and end boundary of 
    	// the trigger to activate and deactivate the trigger. The start 
    	// boundary specifies when the task's action will be performed.
    	$oTrigger = $this->_oTask->newTrigger($cTriggerType);
    	
		$sStartTime = date('Y-m-d', $xTrigger) . 'T' . date('H:i:s', $xTrigger);
		// @todo make schedule period configurable
		$sEndTime = date('Y-m-d', $xTrigger+$xTriggerEnd) . 'T' . date('H:i:s', $xTrigger+$xTriggerEnd);
    	
    	$oTrigger->StartBoundary = $sStartTime;
		$oTrigger->EndBoundary = $sEndTime;
		
		// default to 5 minute exicution limit
		$oTrigger->setTimeLimit(X_Scheduler_Ms_Trigger::LIMIT_5MINUTES);
		$oTrigger->enable();
		
		// 5. Create an action for the task to execute by using the 
		// TaskDefinition. Actions property. This property provides access to 
		// the ActionCollection object. Use the ActionCollection.Create method 
		// to specify the type of action you want to create. This example uses 
		// an ExecAction object, which represents an action that executes a 
		// command-line operation.
		$this->_oTask->addExecAction($sCommand, 0, $sArgs);
				
		
    }
    /**
     * register the task
     * currently only register by group
     * @todo register by other authenticaiton methods
     * 
     */
    public function register($sName = null)
    {
    	// 6. Register the task using the TaskFolder.RegisterTaskDefinition 
		// method. For this example the task will start Command at the supplied
		// time.
		$vEmpty = X_Ms_Variant::none();
		if ($sName == null)
		{
		    $sName = __CLASS__ . time();		    
		}
		
		$this->getFolder()->RegisterTaskDefinition($sName, $this->_oTask->getVariant(), self::CREATE_OR_UPDATE, $vEmpty, $vEmpty, 3);
		return $sName;
    }
    
    /**
     * setup task collecion
     * 
     * @param int $oVisibility
     */
    public function setTaskCollection($oVisibility = 0)
    {
    	// make sure we have a folder to list
        if (empty($this->_oFolder)) $this->getFolder();
        
        $this->_oTaskCollection = $this->_oFolder->GetTasks($oVisibility);
    }
    
    /**
     * get a list of tasks
     *
     * @param int $oVisibility
     * @return array
     */
    public function getTasks($oVisibility = 0)
    {
    	$this->setTaskCollection($oVisibility);
    	#if (empty($this->_oTaskCollection)) $this->setTaskCollection($oVisibility);
    	       
        $aTasks = array();
        foreach ($this->_oTaskCollection as $vItem)
        {
            $aTasks[] = new X_Scheduler_Ms_Task($this,$vItem);
        }
        return $aTasks;
    }
    /**
     * get 2D task array
     * 
     * @param int $oVisibility
     */
    public function getTaskArray($oVisibility = 0)
    {
    	$this->setTaskCollection($oVisibility);
    	
    	$aReturnTasks = array();
    	
    	foreach ($this->_oTaskCollection as $vTask)
    	{
    		$oTask = new X_Scheduler_Ms_Task($this, $vTask);    		
    		$aTask = $oTask->toArray();    		
    		$aReturnTasks[$vTask->Name] = $aTask;
    	}
    	
    	return $aReturnTasks;
    }
    /**
     * return the named task
     * 
     * @param string $sName
     * @param int $oVisibility
     * @return X_Scheduler_Ms_Task
     */
    public function getTaskByName($sName, $oVisibility = 0)
    {
    	$this->setTaskCollection($oVisibility);
        
        $sLowerCaseName = strtolower($sName);
        
        foreach ($this->_oTaskCollection as $oItem)
        {
        	if (strtolower($oItem->Name) == $sLowerCaseName)
        	{
        		return new X_Scheduler_Ms_Task($this, $oItem);
        	}
        }
        
        return false;        
    }
    /**
     * get the task count from the task collection
     *
     * @return int
     */
    public function count()
    {
        if (empty($this->_oTaskCollection)) $this->getTasks();
        return (int)$this->_oTaskCollection->Count;
    }
    /**
     * simple debug function
     *
     * @return string
     */
    public function debug()
    {
        return X_Ms_Com::debug($this->_oVariant, "Schedule.Service");
    }
}