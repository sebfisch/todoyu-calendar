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
 * Calendar Portal Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPortalRenderer {

	/**
	 * Get tab label for appointment tab in portal
	 *
	 * @param	Boolean		$count		Add count in brackets
	 * @return	String
	 */
	public static function getAppointmentTabLabel($count = true) {
		$label		= TodoyuLabelManager::getLabel('calendar.ext.portal.tab.appointments');

		if( $count ) {
			$events	= TodoyuCalendarPortalManager::getAppointments();

			$label		= $label . '(' . sizeof($events) . ')';
		}

		return $label;
	}



	/**
	 * Get tab content for appointment tab in portal
	 *
	 * @return	String
	 */
	public static function getAppointmentTabContent() {
		$config		= Todoyu::$CONFIG['EXT']['calendar']['appointmentTabConfig'];
		$idPerson	= TodoyuAuth::getPersonID();

			// Get events
		$events		= TodoyuCalendarPortalManager::getAppointments();

		if( $config['showHoliday'] ) {
			$holidays		= TodoyuCalendarPortalManager::getHolidays();
		} else {
			$holidays	= array();
		}

		if( $config['showBirthday'] ) {
			$birthdays		= TodoyuCalendarPortalManager::getBirthdays();
		} else {
			$birthdays	= array();
		}

			// Add details if expanded
		foreach($events as $idEvent => $eventData) {
			if( TodoyuCalendarPreferences::getPortalEventExpandedStatus($eventData['id']) ) {
				$events[$idEvent]['details'] = TodoyuCalendarEventRenderer::renderEventDetailsInList($eventData['id']);
			}
		}

		$tmpl	= 'ext/calendar/view/tab-portal-eventslist.tmpl';
		$data	= array(
			'events'		=> $events,
			'showHolidays'	=> $config['showHoliday'],
			'holidays'		=> $holidays,
			'showBirthdays'	=> $config['showBirthday'],
			'birthdays'		=> $birthdays,
			'color'			=> TodoyuColors::getColorIndex($idPerson),
			'javascript'	=> 'Todoyu.Ext.calendar.ContextMenuEventPortal.attach();Todoyu.Ext.calendar.installQuickinfos();'
		);

		return render($tmpl, $data);
	}

}

?>