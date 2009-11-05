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
	 * @param	Bool		$unique
	 * @param	Integer		$idArea
	 * @param	Integer		$idUser
	 */
	public static function savePref($preference, $value, $idItem = 0, $unique = false, $idArea = 0, $idUser = 0) {
		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, $preference, $value, $idItem, $unique, $idArea, $idUser);
	}



	/**
	 * Get calendar extension preference
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Boolean		$unserialize
	 * @param	Integer		$idUser
	 * @return	String
	 */
	public static function getPref($preference, $idItem = 0, $idArea = 0, $unserialize = false, $idUser = 0) {
		return TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, $preference, $idItem, $idArea, $unserialize, $idUser);
	}



	/**
	 * Get calendar extension preferences
	 *
	 * @param	String		$preference
	 * @param	Integer		$idItem
	 * @param	Integer		$idArea
	 * @param	Integer		$idUser
	 * @return	Array
	 */
	public static function getPrefs($preference, $idItem = 0, $idArea = 0, $idUser = 0) {
		return TodoyuPreferenceManager::getPreferences(EXTID_CALENDAR, $preference, $idItem, $idArea, $idUser);
	}


	/**
	 * Get date for calendar
	 *
	 * @param	Integer	$idArea
	 * @return	Integer
	 */
	public static function getDate($idArea = 0) {
		$date	= self::getPref('date', 0, $idArea);

		if( $date === false ) {
			$date = NOW;
		}

		return $date;
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


	public static function saveFullDayView($full = true) {
		$full	= $full ? 1 : 0;

		self::savePref('fulldayview', $full, 0, true);
	}

	public static function getFullDayView() {
		$pref	= self::getPref('fulldayview', 0);

		return intval($pref) === 1;
	}



	/**
	 * Save selected event types
	 *
	 * @param	Array		$types
	 */
	public static function saveEventTypes($types) {
		$types	= TodoyuArray::intval($types);
		$types	= implode(',', $types);

		self::savePref('panelwidget-eventtypeselector', $types, 0, true, AREA);
	}



	/**
	 * Save selected holiday sets
	 *
	 * @param	Array	$setIDs
	 */
	public static function saveHolidaySets($setIDs) {
		$setIDs	= TodoyuArray::intval($setIDs);

			// 'no set'-option selected? deselect all other options
		if (in_array(0, $setIDs)) {
			$setIDs	= array(0);
		}

		$setIDs	= implode(',', $setIDs);

		self::savePref('panelwidget-holidaysetselector', $setIDs, 0, true, AREA);
	}



	/**
	 * Gets the current active tab
	 * @return	String	tab name
	 */
	public static function getActiveTab() {
		$tab	= TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, 'tab');

		if( $tab === false ) {
			$tab = $GLOBALS['CONFIG']['EXT']['calendar']['config']['defaultTab'];
		}

		return $tab;
	}



	/**
	 * Saves the current active tab
	 *
	 * @param 	String	 $idTab		Name of the tab
	 */
	public static function saveActiveTab($tabKey) {
		self::savePref('tab', $tabKey, 0, true);
	}



	/**
	 * Gets the saved calendar date. If not set, return now
	 *
	 * @param	Integer	$idArea
	 * @return	Integer	Timestamp
	 */
	public static function getCalendarDate( $idArea = 0 ) {
		$tstamp	= TodoyuPreferenceManager::getPreference(EXTID_CALENDAR, 'date', 0, $idArea);

		if( $tstamp === false ) {
			$tstamp = NOW;
		}

		return $tstamp;
	}



	/**
	 * Saves the active calendar date.
	 *
	 * @param	Integer	$idArea
	 * @param	Integer	$tstamp			UNIX Timestamp
	 */
	public static function saveCalendarDate( $idArea, $tstamp ) {
		$tstamp	= intval($tstamp);

		TodoyuPreferenceManager::savePreference(EXTID_CALENDAR, 'date', $tstamp, 0, true, $idArea);
	}


 }

?>