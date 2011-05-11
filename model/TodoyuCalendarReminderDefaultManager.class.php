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
 * Manage reminder defaults in profile
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuCalendarReminderDefaultManager {

	/**
	 * Get default reminder time for email
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getEmailDefaultAdvanceTime($idPerson = 0) {
		return self::getDefaultAdvanceTime(CALENDAR_TYPE_EVENTREMINDER_EMAIL, $idPerson);
	}



	/**
	 * Get default reminder time for popup
	 *
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getPopupDefaultAdvanceTime($idPerson = 0) {
		return self::getDefaultAdvanceTime(CALENDAR_TYPE_EVENTREMINDER_POPUP, $idPerson);
	}



	/**
	 * Check whether default email reminder is enabled
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isEmailDefaultActivationEnabled($idPerson = 0) {
		return self::isDefaultActivationEnabled(CALENDAR_TYPE_EVENTREMINDER_EMAIL, $idPerson);
	}



	/**
	 * Check whether default popup reminder is enabled
	 *
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isPopupDefaultActivationEnabled($idPerson = 0) {
		return self::isDefaultActivationEnabled(CALENDAR_TYPE_EVENTREMINDER_POPUP, $idPerson);
	}



	/**
	 * Get default advance (time before event) reminding time of given reminder type and person
	 *
	 * @param	Integer		$type			Reminder type (constant)
	 * @param	Integer		$idPerson
	 * @return	Integer
	 */
	public static function getDefaultAdvanceTime($type, $idPerson = 0) {
		$type	= intval($type);
		$idPerson		= Todoyu::personid($idPerson);

		$typePrefix	= TodoyuCalendarReminderManager::getReminderTypePrefix($type);
		$preference	= 'reminder' . $typePrefix . '_advancetime';
		$value		= TodoyuCalendarPreferences::getPref($preference, 0, 0, false, $idPerson);

		return intval($value);
	}



	/**
	 * Check whether default values for reminder is enabled
	 *
	 * @param	Integer		$type
	 * @param	Integer		$idPerson
	 * @return	Boolean
	 */
	public static function isDefaultActivationEnabled($type, $idPerson) {
		$idPerson	= Todoyu::personid($idPerson);
		$typePrefix	= TodoyuCalendarReminderManager::getReminderTypePrefix($type);
		$preference	= 'is_reminder' . $typePrefix . 'active';

		$value		= TodoyuCalendarPreferences::getPref($preference, 0, 0, false, $idPerson);

		return $value ? true : false;
	}

}

?>