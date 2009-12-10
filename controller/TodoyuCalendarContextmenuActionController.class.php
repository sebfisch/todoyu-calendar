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
 * Contextmenu action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarContextmenuActionController extends TodoyuActionController {

	/**
	 *	Init
	 *
	 *	@param	Array	$params
	 */
	public function init(array $params) {
		restrict('calendar', 'use');
	}



	/**
	 * Contextmenu for calendar area
	 *
	 * @param	Array		$params
	 */
	public function areaAction(array $params) {
		$time		= intval($params['time']);
		$contextMenu= new TodoyuContextMenu('CalendarArea', $time);

		$contextMenu->printJSON();
	}



	/**
	 * Contextmenu for events in calendar
	 *
	 * @param	Array		$params
	 */
	public function eventAction(array $params) {
		$idEvent	= intval($params['event']);

		$contextMenu= new TodoyuContextMenu('Event', $idEvent);

		$contextMenu->printJSON();
	}



	/**
	 * Contextmenu for events in portal
	 *
	 * @param	Array		$params
	 */
	public function eventPortalAction(array $params)	{
		$idEvent	= intval($params['event']);

		$contextMenu= new TodoyuContextMenu('EventPortal', $idEvent);

		$contextMenu->printJSON();
	}

}

?>