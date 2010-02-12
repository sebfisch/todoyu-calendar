<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 snowflake productions gmbh
*  All rights reserved
*
*  This script is part of the todoyu project.
*  The todoyu project is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License, version 2,
*  (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html) as published by
*  the Free Software Foundation;
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Records action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarRecordsActionController extends TodoyuActionController {

	/**
	 * Render sub part to calendar admin form for record types added/ used by calendar (holiday, holidayset)
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public static function addSubformAction(array $params) {
		$xmlBase 	= 'ext/calendar/config/form/admin';

		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);

		switch($fieldName) {
			case 'holiday':
				$xmlPath	= $xmlBase . '/holidayset.xml';
				break;

			case 'holidayset':
				$xmlPath	= $xmlBase . '/holiday.xml';
				break;
		}

		$form		= TodoyuFormManager::getForm($xmlPath, $idRecord);

			// Load form data
		$formData	= $form->getFormData();
		$formData	= TodoyuFormHook::callLoadData($xmlPath, $formData, $idRecord);

		return TodoyuFormManager::renderSubformRecord($xmlPath, $fieldName, $formName, $index, $idRecord, $formData);
	}

}

?>