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
 * Quickevent action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuCalendarQuickeventActionController extends TodoyuActionController {



	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function popupAction(array $params) {
		$time	= intval($params['time']);
		$time	= TodoyuTime::getStartOfDay($time);
		$time	= $time + 8 * 3600;

		return TodoyuEventRenderer::renderCreateQuickEvent($time);
	}



	/**
	 * Save quickevent (quickevent popup)
	 *
	 *
	 * @param array $params
	 * @return unknown
	 */
	public function saveAction(array $params) {
		$formData	= $params['quickevent'];
		$xmlPath	= 'ext/calendar/config/form/quickevent.xml';
		$form		= new TodoyuForm($xmlPath);
		$form		= TodoyuFormHook::callBuildForm($xmlPath, $form, 0);

		$form->setFormData($formData);

		if( $form->isValid() ) {
			$storageData	= $form->getStorageData();

				// Save or update event
			$idEvent	= TodoyuEventManager::saveQuickEvent($storageData);
			$event		= TodoyuEventManager::getEvent($idEvent);

				// Send back start date to jump in the calendar view
			return $event->getStartDate();
		} else {
			TodoyuHeader::sendTodoyuErrorHeader();

			$form->setUseRecordID(false);

			return $form->render();
		}
	}

}

?>