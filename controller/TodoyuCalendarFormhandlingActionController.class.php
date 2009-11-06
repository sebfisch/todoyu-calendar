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
	 *	'addSubForm' action method
	 *
	 *	@param	Array	$params
	 *	@return String
	 */
	public static function addSubformAction(array $params) {
		$index		= intval($params['index']);
		$fieldName	= $params['field'];
		$formName	= $params['form'];
		$idRecord	= intval($params['record']);

		if( $formName === 'record' ) {
			$xmlBase	= 'ext/calendar/config/form/admin/';

			switch($fieldName) {
				case 'holidayset':
					$xmlPath = $xmlBase . 'holiday.xml';
					break;
			}

		} else {
			$xmlBase	= 'ext/calendar/config/form/';

			switch($fieldName) {
				case 'user':
					$xmlPath = $xmlBase . $formName . '.xml';
					break;
			}

		}

		$form 	= new TodoyuForm($xmlPath);
		$form	= TodoyuFormHook::callBuildForm($xmlPath, $form, $index);

			// Load form data
		$data	= TodoyuFormHook::callLoadData($xmlPath, array(), $idRecord);

		$form->setName($formName);
		$form->setFormData($data);

		return $form->getField($fieldName)->renderNewRecord($index);
	}

}

?>