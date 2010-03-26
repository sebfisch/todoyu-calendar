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
 * Calendar specific Dwoo plugins
 *
 * @package		Todoyu
 * @subpackage	Template
 */

/**
 * Check right of current person to see given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo 		$dwoo
 * @param	Integer		$idEvent
 * @return	Boolean
 */
function Dwoo_Plugin_isAllowedSeeEvent(Dwoo $dwoo, $idEvent) {
	return TodoyuEventRights::isSeeAllowed($idEvent);
}



/**
 * Check right of current person to add events
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo 		$dwoo
 * @return	Boolean
 */
function Dwoo_Plugin_isAllowedAddEvent(Dwoo $dwoo) {
	return TodoyuEventRights::isAddAllowed();
}



/**
 * Check right of current person to edit given event
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param	Dwoo 		$dwoo
 * @param	Integer		$idEvent
 * @return	Boolean
 */
function Dwoo_Plugin_isAllowedEditEvent(Dwoo $dwoo, $idEvent) {
	return TodoyuEventRights::isEditAllowed($idEvent);
}



/**
 * Get label of eventtype
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo_Compiler 	$compiler
 * @param 		Integer			$idEventIndex
 * @return		String
 */
function Dwoo_Plugin_EventTypeLabel_compile(Dwoo_Compiler $compiler, $idEventIndex) {
	return 'TodoyuEventTypeManager::getEventTypeLabel(' . $idEventIndex . ')';
}



/**
 * Get key of eventtype
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo_Compiler 	$compiler
 * @param		Integer			$idEventIndex
 * @return		String
 */
function Dwoo_Plugin_EventTypeKey_compile(Dwoo_Compiler $compiler, $idEventIndex) {
	return 'TodoyuEventTypeManager::getEventTypeKey(' . $idEventIndex . ')';
}



/**
 * Get short name label of day name, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo 		$dwoo
 * @param 		Integer		$dayNum
 * @return		String
 */
function Dwoo_Plugin_weekdayName(Dwoo $dwoo, $timestamp) {
	$timestamp	= intval($timestamp);

	return Label( 'date.weekday.' . strtolower(date('l', $timestamp)) );
}


/**
 * Get short name label of day name, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo 		$dwoo
 * @param 		Integer		$dayNum
 * @return		String
 */
function Dwoo_Plugin_weekdayNameShort(Dwoo $dwoo, $timestamp) {
	$timestamp	= intval($timestamp);

	return Label( 'date.weekday.' . strtolower(date('D', $timestamp)) );
}



/**
 * Assign negative timestamps. Needed if first timestamp would be after first day of month
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 		Dwoo		$dwoo
 * @param 		Array		$timestamps
 */
function Dwoo_Plugin_assignNegativeTimestamps(Dwoo $dwoo, $month, $timestamps) {
	$year	= date('Y', $timestamps[1]);
	$day	= 1;

	for ($i = 7; $i > 0; $i--) {
		$timestamps[(93 + $i)]	= mktime(0, 0, 0, $month, $day, $year);
		$day -= 1;
	}

	$dwoo->assignInScope($timestamps, 'timestamps');
}

?>