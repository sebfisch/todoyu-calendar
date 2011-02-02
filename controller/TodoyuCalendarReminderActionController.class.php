<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2011, snowflake productions GmbH, Switzerland
* All rights reserved.
*
* This script is part of the todoyu project.
* The todoyu project is free software; you can redistribute it and/or modify
* it under the terms of the BSD License.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the BSD License
* for more details.
*
* This copyright notice MUST APPEAR in all copies of the script.
*****************************************************************************/

/**
 * Reminder action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarReminderActionController extends TodoyuActionController {

	/**
	 * Initialize (restrict rights)
	 */
	public function init() {
		restrict('calendar', 'general:use');
	}



	/**
	 * Render event reminder for display in popUp
	 *
	 * @param	Array	$params
	 * @return	String
	 */
	public function popupAction(array $params) {
		$idEvent	= intval($params['event']);

		$isAudioActivated	= TodoyuExtConfManager::getExtConfValue('calendar', 'audioreminder_active') ? true : false;
		if( $isAudioActivated ) {
			$soundFilename	= TodoyuReminderManager::getSoundFilename($idEvent);
			if( $soundFilename !== false ) {
				TodoyuHeader::sendTodoyuHeader('soundfile', $soundFilename);
			}
		}

		return TodoyuEventRenderer::renderEventReminder($idEvent);
	}



	/**
	 * Dismiss given event reminder
	 *
	 * @param	Array	$params
	 */
	public function dismissAction(array $params) {
		$idEvent	= intval($params['event']);

		TodoyuReminderManager::setReminderDismissed($idEvent);
	}



	/**
	 * Reschedule given event reminder for later popping up again
	 *
	 * @param	Array	$params
	 */
	public function rescheduleAction(array $params) {
		$idEvent		= intval($params['event']);
		$nextShowTime	= NOW + intval($params['delay']);

		TodoyuReminderManager::rescheduleReminder($idEvent, $nextShowTime);
	}

}

?>