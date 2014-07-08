<?php
include_once 'modifier.APPLR_html_entity_decode.php';
include_once 'function.APPLR_FCKeditor.php';

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */

/**
 * APPLR FCKeditor function plugin
 *
 * Type:     function<br>
 * Name:     APPLR_FCKeditor<br>
 * Purpose:  print out a FCKeditor box
 *
 * @author Diego Flores <diegotf [at] gmail [dot] com>
 * 
 * @param array                    $params   parameters
 * 
 * @return mixed
 */
function smarty_function_APPLR_FormField($params,$template) {
	if(empty($params['field']) || !is_object($params['field'])) return;
	
	$strHTML	= smarty_modifier_APPLR_html_entity_decode($params['field']->html);
	$strName	= $params['field']->field_name;
	$strType	= $params['field']->fieldtype;
	$strSuffix	= end(explode('_',$strName));

	$template->assign('evalAssign','');
	switch($params['field']->sec_struct_id) {
		// RICHTEXT
		case 3:
			smarty_function_APPLR_FCKeditor(array('name' => $strName, 'value' => $params['value']));
			return;
		break;
		
		// PHONE
		case 6:
			$strField	= str_replace(array('name="','id="','_ddd" value="','value=""'),array('name="' . $strName, 'id="' . $strName, '_ddd" value="' . $params['value']->original[0], 'value="' . $params['value']->original[1] . '"'), $strHTML);
			return $strField;
		break;
		
		// UPLOAD
		case 8:
			$strField	= str_replace(array('name="','id="','_old" value="'),array('name="' . $strName, 'id="' . $strName,'_old" value="' . $params['value']), $strHTML);
			return $strField;
		break;
		
		// SEX
		case 7:
		// BOOLEAN
		case 9:
		// CHECKBOX
		case 13:
		// TREATMENT TITLE
		case 14:
		// DAY PERIOD
		case 15:
			$strField	= str_replace(array('name="','id="','value="' . $params['value']),array('name="' . $strName, 'id="' . $strName, 'checked value="' . $params['value']), $strHTML);
			return $strField;
		break;
		
		// DATE
		case 10: 
		// TIME
		case 11:
			var_dump($params['value']->original->formatted);
			$strField	= str_replace(array('#name#','prefix="'),array($strName,'time="' . (!empty($params['value']) ? $params['value']->original->Timestamp : null) . '" prefix="' . $strName), $strHTML);
			$template->assign('evalAssign',$strField);
			return;
		break;
		
		// CURRENCY
		case 18:
			$strField	= str_replace(array('name="','id="','value="' . $params['value']->intval),array('name="' . $strName, 'id="' . $strName, 'checked value="' . $params['value']->intval), $strHTML);
			return $strField;
		break;
		
		// ZIPCODE
		case 19:
			$strField	= str_replace(array('name="','id="','value="'),array('name="' . $strName, 'id="' . $strName, 'value="' . $params['value']->original), $strHTML);
			return $strField;
		break;
		
		// DEFAULT FIELD
		default:
			$strField	= str_replace(array('#name#','name="','id="','for="','value="','</textarea'),array($strName, 'name="'.$strName, 'id="'.$strName, 'for="'.$strName, 'value="'.$params['value'], $params['value'].'</textarea'), $strHTML);
		    return $strField;
		break;
	}
}
?>