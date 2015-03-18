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
class X_Ms_Variant
{
    /**
     * variant instance to wrap
     *
     * @var VARIANT
     */
    protected $_oVariant;
    
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
        if ($oVariant) $this->setVariant($oVariant);
    }
    
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
    
    
    public function validateXmlTime($sTime)
    {
    	$sFiltered = X_Filter_Period::go($sTime);
    	return !empty($sFiltered);
    }
    
    /**
     * get wrapper for the internal variant
     *
     * @param string $sName
     * @return unknown
     */
    function __get($sName)
    {
        return $this->_oVariant->$sName;
    }
    /**
     * set wrapper for the internal variant
     *
     * @param string $sName
     * @param unknown_type $xValue
     */
    function __set($sName, $xValue)
    {
        $this->_oVariant->$sName = $xValue;
    }
    
    /**
     * return a blank variant
     * for use in place of null
     *
     * @return VARIANT
     */
    static public function none()
    {
        return new VARIANT();
    }
    
    

}