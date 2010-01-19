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
 * Calendar Portal Renderer
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarPortalRenderer {

	/**
	 * Get tab label for appointment tab in portal
	 *
	 * @param	Bool		$count		Add count in brackets
	 * @return	String
	 */
	public static function getAppointmentTabLabel($count = true) {
		$label		= TodoyuLocale::getLabel('portal.tab.appointments');

		if ( $count ) {
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
		$config	= $GLOBALS['CONFIG']['EXT']['calendar']['appointmentTabConfig'];
		$idUser	= userid();

			// Get events
		$events	= TodoyuCalendarPortalManager::getAppointments();

		if ( $config['showHoliday'] ) {
			$holidays		= TodoyuCalendarPortalManager::getHolidays();
		} else {
			$holidays	= array();
		}

		if ( $config['showBirthday'] ) {
			$birthdays		= TodoyuCalendarPortalManager::getBirthdays();
		} else {
			$birthdays	= array();
		}

		$color = TodoyuEventRenderer::getEventColorData($idUser);

			// Add details if expanded
		foreach($events as $idEvent => $eventData) {
			if( TodoyuCalendarPreferences::getPortalEventExpandedStatus($eventData['id']) ) {
				$events[$idEvent]['details'] = TodoyuEventRenderer::renderEventDetailsInList($eventData['id']);
			}
		}


		$tmpl	= 'ext/calendar/view/tab-portal-eventslist.tmpl';
		$data	= array(
			'events'						=> $events,
			'showHolidays'					=> $config['showHoliday'],
			'holidays'						=> $holidays,
			'showBirthdays'					=> $config['showBirthday'],
			'birthdays'						=> $birthdays,
			'color'							=> $color[$idUser],
			'javascript'					=> 'Todoyu.Ext.calendar.ContextMenuEventPortal.attach();'
		);

//		TodoyuDebug::printHtml($data['events']);

		return render($tmpl, $data);
	}

}

?>