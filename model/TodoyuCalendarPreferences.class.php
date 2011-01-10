<?php
/****************************************************************************
* todoyu is published under the BSD License:
* http://www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2010, snowflake productions GmbH, Switzerland
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
 * Calendar Preferences
 *
 * @package		Todoyu
 * @subpackage	Calendar
*/
class TodoyuCalendarPreferences {

	/**
	 * Save calendar extension preference
	 *
	 * @param	Integer		$preference
	 * @param	String		$value
	 * @param	Integer		$idItem
	 * @param	Boolean		$unique
	 * @param	Integer		$idArea
	 * @param	Integer		$idPerson
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idPerson = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, $preference, $value, $idItem, $unique, $idArea, $idPerson);
	}



	/**
	 * Get given calendar extension preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Boolean		$unserialize
	 * @param	Integer		$idPerson
	 * @return	String
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, $preference, $idItem, $idArea, $unserialize, $idPerson);
	}



	/**
	 * Get calendar extension preferences
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idPerson
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idPerson = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_CALENDAR, $preference, $idItem, $idArea, $idPerson);
	}



	/**
	 * Get date for calendar
	 *
	 * @param	Integer	$idArea
	 * @return	Integer
	 */
	public static function getDate($idArea = 0) {
		$date	= self::getPref('date', 0, $idArea);

		return $date === false ? NOW : $date;
	}



	/**
	 * Save date for calendar
	 *
	 * @param	Integer		$date
	 * @param	Integer		$idArea
	 */
	public static function saveDate($date, $idArea = 0) {
		$date	= intval($date);

		self::savePref('date', $date, 0, true, $idArea);
	}



	/**
	 * Save fullday view preference (active?)
	 *
	 * @param	Boolean		$full
	 */
	public static function saveFullDayView($full = true) {
		$full	= $full ? 1 : 0;

		self::savePref('fulldayview', $full, 0, true);
	}



	/**
	 * Get fullday view (active?) preference
	 *
	 * @return	Boolean
	 */
	public static function getFullDayView() {
		$pref	= self::getPref('fulldayview', 0);

		return intval($pref) === 1;
	}



	/**
	 * Save selected event types
	 *
	 * @param	Array		$types
	 */
	public static function saveEventTypes(array $eventTypes) {
		$eventTypes	= implode(',', $eventTypes);

		self::savePref('panelwidget-eventtypeselector', $eventTypes, 0, true, AREA);
	}



	/**
	 * Save selected holiday sets
	 *
	 * @param	Array	$setIDs
	 */
	public static function saveHolidaySets($setIDs) {
		$setIDs	= TodoyuArray::intval($setIDs);

			// 'no set'-option selected? deselect all other options
		if(in_array(0, $setIDs)) {
			$setIDs	= array(0);
		}

		self::savePref('panelwidget-holidaysetselector', implode(',', $setIDs), 0, true, AREA);
	}



	/**
	 * Gets the current active tab
	 * @return	String	tab name
	 */
	public static function getActiveTab() {
		$tab	= TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, 'tab');

		return $tab === false ? Todoyu::$CONFIG['EXT']['calendar']['config']['defaultTab'] : $tab;
	}



	/**
	 * Save the current active tab as pref
	 *
	 * @param 	String	 $idTab		Name of the tab
	 */
	public static function saveActiveTab($tabKey) {
		self::savePref('tab', $tabKey, 0, true);
	}



	/**
	 * Get the saved calendar date pref. If not set, return timestamp of now
	 *
	 * @param	Integer		$idArea
	 * @return	Integer					Timestamp
	 */
	public static function getCalendarDate($idArea = 0) {
		$timestamp	= TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, 'date', 0, $idArea);

		return $timestamp === false ? NOW : $timestamp;
	}



	/**
	 * Save the active calendar date.
	 *
	 * @param	Integer		$idArea
	 * @param	Integer		$timestamp		UNIX Timestamp
	 */
	public static function saveCalendarDate($idArea, $timestamp) {
		$timestamp	= intval($timestamp);

		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, 'date', $timestamp, 0, true, $idArea);
	}



	/**
	 * Save event display preference: expanded?
	 *
	 * @param	Integer		$idEvent
	 * @param	Boolean		$expanded
	 */
	public static function savePortalEventExpandedStatus($idEvent, $expanded = true) {
		$idEvent= intval($idEvent);
		$value	= $expanded ? 1 : 0;

		self::savePref('portal-event-expanded', $value, $idEvent, true);
	}



	/**
	 * Get event display preference: expanded?
	 *
	 * @param	Integer		$idEvent
	 * @return	Boolean
	 */
	public static function getPortalEventExpandedStatus($idEvent) {
		$idEvent= intval($idEvent);

		return intval(self::getPref('portal-event-expanded', $idEvent)) === 1;
	}

 }

?>