<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions gmbh
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
 * Contextmenu action controller
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarContextmenuActionController extends TodoyuActionController {

	/**
	 * Init
	 *
	 * @param	Array	$params
	 */
	public function init(array $params) {
		restrict('calendar', 'general:use');
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