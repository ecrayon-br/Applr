<?php
include_once 'modifier.APPLR_html_entity_decode.php';
include_once 'function.APPLR_FCKeditor.php';
include_once 'function.html_options.php';

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
	
	// RELATIONSHIP FIELDS
	if($params['field']->type == 2) {
		
		$objSection = new SectionContent_controller(false,$params['field']->child_id);
		$objList	= $objSection->content();

		#echo '<pre>'; print_r($params['field']); var_dump($params['value']); echo '</pre>';
		
		foreach($params['value'] AS &$mxdValue) {
			$mxdValue = $mxdValue->id;
		}
		
		$objSection->objSmarty->assign('objList',$objList);
		$objSection->objSmarty->assign('objParams',(object) $params['field']);
		$objSection->objSmarty->assign('arrMatch',$params['value']);
		
		$objSection->renderTemplate(true,'SectionContent_Relationship_form.html');
		
	// CONTENT FIELDS
	} else {
		$template->assign('evalAssign','');
		switch($params['field']->suffix) {
			// 	TEMPLATE REL
			case 'tpl':
				$objTPL 	= new Template_controller(false);
				$objList	= $objTPL->content();
				$arrCombo	= array('Template' => array(0 => 'Selecione'));
				foreach($objList AS $objTemp) {
					$arrCombo[$objTemp->name][$objTemp->id] =  $objTemp->filename; 
				}
				
				$arrParams	= array(
								'name'		=> $strName,
								'selected'	=> $params['value'],
								'options'	=> $arrCombo
								);
				
				return smarty_function_html_options($arrParams,$template);
			break;
			
			// RICHTEXT
			case 'richtext':
				smarty_function_APPLR_FCKeditor(array('name' => $strName, 'value' => smarty_modifier_APPLR_html_entity_decode($params['value'])));
				return;
			break;
			
			// PHONE
			case 'phone':
				$strField	= str_replace(array('name="','id="','_ddd" value="','value=""'),array('name="' . $strName, 'id="' . $strName, '_ddd" value="' . $params['value']->original[0], 'value="' . $params['value']->original[1] . '"'), $strHTML);
				return $strField;
			break;
			
			// UPLOAD
			case 'upload':
				$strField	= str_replace(array('name="','id="','_old" value="','#content#'),array('name="' . $strName, 'id="' . $strName, '_old" value="' . $params['value']->original, $params['value']->uri), $strHTML);
				return $strField;
			break;
			
			// SEX
			case 'sex':
			// TREATMENT TITLE
			case 'title':
			// DAY PERIOD
			case 'period':
				$strField	= str_replace(array('name="','id="','value="' . $params['value']->intval),array('name="' . $strName, 'id="' . $strName, 'checked value="' . $params['value']->intval), $strHTML);
				return $strField;
			break;
			
			// PASSWORD
			case 'pwd':
				$strField	= str_replace(array('#name#','name="','id="','value="'),array($strName,'name="' . $strName, 'id="' . $strName, 'value="' . $params['value']->original), $strHTML);
				return $strField;
			break;
				
			// BOOLEAN
			case 'bool':
			// CHECKBOX
			case 'check':
				$strField	= str_replace(array('name="','id="','value="' . $params['value']),array('name="' . $strName, 'id="' . $strName, 'checked value="' . $params['value']), $strHTML);
				return $strField;
			break;
			
			// DATE
			case 'date':
				$strField	= str_replace(array('#name#','prefix="'),array($strName,'time=' . (empty($params['value']->original->Timestamp) ? 'null' : '"' . $params['value']->original->Timestamp . '000000"') . ' prefix="' . $strName), $strHTML);
				$template->assign('evalAssign',$strField);
				return;
			break;
			 
			// TIME
			case 'time':
				$strField	= str_replace(array('#name#','prefix="'),array($strName,'time=' . (empty($params['value']->original->Timestamp) ? 'null' : '"' . date('Ymd') . $params['value']->original->Timestamp . '"') . ' prefix="' . $strName), $strHTML);
				$template->assign('evalAssign',$strField);
				return;
			break;
			
			// CURRENCY
			case 'currency':
				$strField	= str_replace(array('name="','id="','value="' . $params['value']->intval),array('name="' . $strName, 'id="' . $strName, 'checked value="' . $params['value']->intval), $strHTML);
				return $strField;
			break;
			
			// ZIPCODE
			case 'zipcode':
				$strField	= str_replace(array('name="','id="','value="'),array('name="' . $strName, 'id="' . $strName, 'value="' . $params['value']->formatted), $strHTML);
				return $strField;
			break;
			
			// DEFAULT FIELD
			default:
				if(is_string($params['value'])) $strField	= str_replace(array('#name#','name="','id="','for="','value="','</textarea'),array($strName, 'name="'.$strName, 'id="'.$strName, 'for="'.$strName, 'value="'.$params['value'], $params['value'].'</textarea'), $strHTML);
			    return $strField;
			break;
		}
	}
}
?>