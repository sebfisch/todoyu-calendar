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
 * Formhandling action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarFormhandlingActionController extends TodoyuActionController {

	/**
	 * Add subform to a form
	 *
	 * @param	Array		$params
	 * @return	String
	 */
	public static function addSubformAction(array $params) {
		restrictIfNone('calendar', 'event:editAll,event:editAssigned');

		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);

		$xmlBase	= 'ext/calendar/config/form/';

		switch($fieldName) {
			case 'user':
				$xmlPath = $xmlBase . $formName . '.xml';
				break;
		}

			// Load form data
		$data	= TodoyuFormHook::callLoadData($xmlPath, array(), $idRecord);

		return TodoyuFormManager::renderSubformRecord($xmlPath, $fieldName, $formName, $index, $idRecord, $data);
	}

}

?>