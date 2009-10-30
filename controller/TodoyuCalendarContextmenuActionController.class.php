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
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function init(array $params) {
		TodoyuHeader::sendHeaderJSON();
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function areaAction(array $params) {
		$time		= intval($params['time']);
		$contextMenu= new TodoyuContextMenu('CalendarArea', $time);

		return $contextMenu->getJSON();			
	}

	
	
	/**
	 *	@todo	COMMENT
	 *
	 *	@param array $params
	 */
	public function eventAction(array $params) {
		$idEvent	= intval($params['event']);
		$contextMenu= new TodoyuContextMenu('Event', $idEvent);
		
		return $contextMenu->getJSON();		
	}
		
}

?>