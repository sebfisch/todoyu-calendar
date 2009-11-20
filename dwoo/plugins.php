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
 * Calendar specific Dwoo plugins
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 *
 */



/**
 * Get short name label of week, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 	Dwoo 		$dwoo
 * @param 	Integer		$id
 * @return	String
 */
function Dwoo_Plugin_EventTypeLabel(Dwoo $dwoo, $idEventType) {
	$idEventType	= intval($idEventType);

	$designation = $GLOBALS['CONFIG']['EXT']['calendar']['EVENTTYPE'][$idEventType];

	return Label('event.eventtype.' . $designation);
}


/**
 * Get short name label of day name, e.g: 'Mon'
 *
 * @package		Todoyu
 * @subpackage	Template
 *
 * @param 	Dwoo 		$dwoo
 * @param 	Integer		$dayNum
 * @return	String
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
 * @param 	Dwoo 		$dwoo
 * @param 	Integer		$dayNum
 * @return	String
 */
function Dwoo_Plugin_weekdayNameShort(Dwoo $dwoo, $timestamp) {
	$timestamp	= intval($timestamp);

	return Label( 'date.weekday.' . strtolower(date('D', $timestamp)) );
}

?>