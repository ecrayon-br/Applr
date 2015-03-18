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
/**
 * 
 * For use with php COM() calls
 * manages an instance of the OLE compatible COM object after 
 * instanciating it with the module name
 * see: http://www.php.net/manual/en/class.com.php
 * 
 */
abstract class X_Ms_Com_Abstract extends X_Ms_Variant
{
    /**
     * variant instance to wrap
     * also used as the primary subject
     *
     * @var VARIANT
     */
    protected $_oVariant;
    /**
     * name of the module to instanciate
     *
     * @var unknown_type
     */
    protected $_sModuleName;
    
    /**
     * @return VARIANT
     */
    public function getVariant()
    {
        return $this->_oVariant;
    }
    
    /**
     * @param VARIANT $_oVariant
     */
    public function setVariant($oVariant)
    {
        $this->_oVariant = $oVariant;
    }
    
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
    function __construct($xServer = null, $Codepage = null, $TypeLib = null)
    {
        $this->_oVariant = new COM($this->_sModuleName, $xServer, $Codepage, $TypeLib);
    }
        
    /**
     * get a variant parameter
     *
     * @param strign $sName
     * @return unknown
     */
    function __get($sName)
    {
        return $this->_oVariant->$sName;
    }
    /**
     * set a variant parameter
     *
     * @param string $sName
     * @param unknown_type $xValue
     */
    function __set($sName, $xValue)
    {
        $this->_oVariant->$sName = $xValue;
    }
    /**
     * call the variant method
     *
     * @param unknown_type $sName
     * @param unknown_type $aArguments
     */
    function __call($sName, $aArguments)
    {
        call_user_func_array(array($this->_oVariant, $sName), $aArguments);
    }

    static public function none()
    {
        return new VARIANT();
    }
    
    

}