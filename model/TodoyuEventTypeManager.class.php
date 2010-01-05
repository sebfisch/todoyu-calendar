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
 * Calendar eventtype manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuEventTypeManager {

	/**
	 * Add a new eventtype
	 *
	 * @param	String		$key
	 * @param	String		$label
	 */
	public static function addEventType($key, $label) {
		$GLOBALS['CONFIG']['EXT']['calendar']['eventtypes'][$key] = array(
			'key'		=> $key,
			'label'		=> $label
		);
	}



	/**
	 * Get eventtype data
	 *
	 * @param	String		$key
	 * @return	Array
	 */
	public static function getEventType($key) {
		return TodoyuArray::assure($GLOBALS['CONFIG']['EXT']['calendar']['eventtypes'][$key]);
	}



	/**
	 * Get all eventtypes
	 *
	 * @param	Bool		$parseLabels
	 * @return	Array
	 */
	public static function getEventTypes($parseLabels = false) {
		$eventTypes = TodoyuArray::assure($GLOBALS['CONFIG']['EXT']['calendar']['eventtypes']);

		if( $parseLabels ) {
			foreach($eventTypes as $index => $eventType) {
				$eventTypes[$index]['label'] = Label($eventType['label']);
			}
		}

		return $eventTypes;
	}



	/**
	 * Get all eventtype keys
	 *
	 * @return	Array
	 */
	public static function getEventTypeKeys() {
		$eventTypes	= self::getEventTypes(false);

		return TodoyuArray::getColumn($eventTypes, 'key');
	}

}

?>