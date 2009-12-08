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
 * Holiday manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuHolidayManager {

	const TABLE		= 'ext_calendar_holiday';


	/**
	 * Get holiday
	 *
	 * @param	Integer			$idHoliday
	 * @return	TodoyuHoliday
	 */
	public static function getHoliday($idHoliday) {
		$idHoliday	= intval($idHoliday);

		return TodoyuCache::getRecord('TodoyuHoliday', $idHoliday);
	}



	/**
	 * All all holidays
	 *
	 * @return	Array
	 */
	public static function getAllHolidays() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'title';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Save holiday
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function saveHoliday(array $data) {
		$idHoliday	= intval($data['id']);
		$xmlPath	= 'ext/contact/config/form/holiday.xml';

		if( $idHoliday === 0 ) {
			$idHoliday = self::addHoliday();
		}

		$data	= self::saveHolidayForeignRecords($data, $idHoliday);
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idHoliday);

		return self::updateHoliday($idHoliday, $data);
	}



	/**
	 * Add a new holiday record
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addHoliday(array $data = array()) {
		unset($data['id']);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Update a holiday record
	 *
	 * @param	Integer		$idHoliday
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateHoliday($idHoliday, array $data) {
		$idHoliday	= intval($idHoliday);
		unset($data['id']);

		return Todoyu::db()->updateRecord(self::TABLE, $idHoliday, $data);
	}



	/**
	 * Get holiday sets where the holiday is linked in
	 *
	 * @param	Integer		$idHoliday
	 * @return	Array
	 */
	public static function getHolidaySets($idHoliday) {
		$idHoliday	= intval($idHoliday);

		$fields	= '	s.*';
		$table	= '	ext_calendar_holidayset s,
					ext_calendar_mm_holiday_holidayset mm';
		$where	= ' mm.id_holiday	= ' . $idHoliday . ' AND
					mm.id_holidayset= s.id AND
					s.deleted 		= 0';
		$order	= '	s.title';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Save foreign records in the holiday record
	 * - Save linked holiday sets
	 *
	 * @param	Array		$data
	 * @param	Integer		$idHoliday
	 * @return	Array
	 */
	protected static function saveHolidayForeignRecords(array $data, $idHoliday) {
		$idHoliday	= intval($idHoliday);

		self::removeHolidaySets($idHoliday);

		if( is_array($data['holidayset']) ) {
			$holidaySetIDs	= TodoyuArray::getColumn($data['holidayset'], 'id');
			foreach($holidaySetIDs as $idHolidaySet) {
				self::addHolidaySet($idHoliday, $idHolidaySet);
			}
		}
		unset($data['holidayset']);

		return $data;
	}



	/**
	 * Remove all linked holiday sets to the holiday
	 *
	 * @param	Integer		$idHoliday
	 */
	public static function removeHolidaySets($idHoliday) {
		$idHoliday	= intval($idHoliday);

		TodoyuDbHelper::removeMMrelations('ext_calendar_mm_holiday_holidayset', 'id_holiday', $idHoliday);
	}



	/**
	 * Add/link a holiday set to the holiday
	 *
	 * @param	Integer		$idHoliday
	 * @param	Integer		$idHolidaySet
	 */
	public static function addHolidaySet($idHoliday, $idHolidaySet) {
		$idHoliday		= intval($idHoliday);
		$idHolidaySet	= intval($idHolidaySet);

		TodoyuDbHelper::addMMrelation('ext_calendar_mm_holiday_holidayset', 'id_holiday', 'id_holidayset', $idHoliday, $idHolidaySet);
	}



	/**
	 * Delete a holiday
	 *
	 * @param	Integer		$idHoliday
	 * @return	Bool
	 */
	public static function deleteHoliday($idHoliday)	{
		$idHoliday	= intval($idHoliday);

		return Todoyu::db()->deleteRecord(self::TABLE, $idHoliday);
	}



	/**
	 * Get holiday records for admin
	 *
	 * @return	Array
	 */
	public static function getRecords() {
		$holidays	= self::getAllHolidays();
		$records	= array();

		foreach($holidays as $holiday) {
			$records[] = array(
				'id'					=> $holiday['id'],
				'label'					=> $holiday['title'],
				'additionalInformations'=> TodoyuTime::format($holiday['date'], 'date')
			);
		}

		return $records;
	}



	/**
	 * Get IDs of holiday sets of given addresses (IDs)
	 *
	 * @param	Array	$addressIDs
	 * @param	Boolean	$groupAddressesBySet
	 * @return	Array
	 */
	public static function getHolidaySetsOfAddresses(array $addressIDs, $groupAddressesBySet = false) {
		$addressIDs		= TodoyuArray::intval($addressIDs, true, true);

		$fields	= 'id,id_holidayset';
		$table	= 'ext_user_address';
		$where	= ' deleted	= 0' . (count($addressIDs) > 0 ? (' AND id IN (' . implode(',', $addressIDs) . ') ') : '');

		$res	= Todoyu::db()->getArray($fields, $table, $where);

		$holidaySetIDs	= array();

		if (! $groupAddressesBySet) {
				// Just get the holiday set IDs
			foreach($res as $entry) {
				$holidaySetIDs[]	= $entry['id_holidayset'];
			}
			$holidaySetIDs = array_unique($holidaySetIDs);
		} else {
				// Get an array of the address IDs with the IDs of their assigned holidaySets
			foreach($res as $entry) {
				$holidaySetIDs[ $entry['id'] ][]	= $entry['id_holidayset'];
			}
		}

		return $holidaySetIDs;
	}



	/**
	 * Get holidays of given sets in given timespan.
	 *
	 * @param	Intger	$tstampFrom			UNIX timestamp of day at beginning of timespan
	 * @param	Intger	$tstampUntil		UNIX timestamp of day at ending of timespan
	 * @param	Array	$holidaySetIDs
	 */
	public static function getHolidaysInTimespan($dateStart = 0, $dateEnd = 0, array $holidaySetIDs) {
		$holidaySetIDs	= TodoyuArray::intval($holidaySetIDs, true, false);
		$dateStart		= intval($dateStart);
		$dateEnd		= intval($dateEnd);

		if( sizeof($holidaySetIDs) === 0 ) {
			return array();
		}

		$fields	= '	h.*, hhmm.id_holidayset';
		$table	= 	self::TABLE . ' h,
					ext_calendar_mm_holiday_holidayset hhmm';
		$where	= '	h.id		= hhmm.id_holiday AND
					h.deleted	= 0 AND
					hhmm.id_holidayset IN(' . implode(',', $holidaySetIDs) . ') AND
					h.date BETWEEN ' . $dateStart . ' AND ' . $dateEnd;
		$group	= '	h.id';

		return Todoyu::db()->getArray($fields, $table, $where, $group);
	}



	/**
	 * Get holidays of given persons in given timespan
	 *
	 * @param	Array	$userIDs
	 * @param	Integer	$tstampFrom		UNIX timestamp of day at beginning of timespan
	 * @param	Integer	$tstampUntil	UNIX timestamp of day at ending of timespan
	 * @return	Array
	 */
	public static function getPersonHolidaysInTimespan(array $userIDs, $dateStart = 0, $dateEnd = 0) {
		$userIDs		= TodoyuArray::intval($userIDs, true, true);
		$dateStart		= intval($dateStart);
		$dateEnd		= intval($dateEnd);

			// Get working locations (company addresses) of given persons, affected holidaySets of given address IDs
		$addressIDs		= TodoyuUserManager::getWorkaddressIDsOfUsers($userIDs);
		$holidaySetIDs	= self::getHolidaySetsOfAddresses($addressIDs);

			// Get all holidays affected holidaySets in given timespan
		$holidays		= self::getHolidaysInTimespan($dateStart, $dateEnd, $holidaySetIDs);

//		TodoyuDebug::printHtml($holidays);

		return $holidays;
	}



	/**
	 * Autocomletes holidays
	 *
	 * @param	String	$sword
	 * @return	Array
	 */
	public static function autocompleteHolidays($sword)	{
		$swordArray = TodoyuDiv::trimExplode(' ', $sword, true);
		$results	= array();

		if(count($swordArray) > 0)	{
			$where = '';
			if( $swordArray[0] != '*' )	{
				$where = Todoyu::db()->buildLikeQuery($swordArray, array('title', 'description'));
			}

			$res = Todoyu::db()->doSelect('id, title, date', self::TABLE, $where, '', 'date DESC');

			while($row = Todoyu::db()->fetchAssoc($res))	{
				$results[$row['id']] = $row['title'] . ' - ' . date(TodoyuLocale::getLabel('core.dateFormat'), $row['date']);
			}

			return $results;

		} else {
			return array();
		}
	}



	/**
	 * Gets the holiday label from given ID
	 *
	 *
	 * @param	Integer	$holidayID
	 * @return	String
	 */
	public static function getHolidayLabel($idHoliday)	{
		$idHoliday	= intval($idHoliday);

		$holiday	= self::getHoliday($idHoliday);

		return $holiday->getLabel();
	}



	/**
	 * Compile array of holidays into an array of days,
	 * (key is date of resp. day) with holidays happening that day in a subarray
	 *
	 * @param	Integer	$tstampFirstDay	timestamp of the first day to start the events per day grouping with
	 * @param	Array	$eventsUngrouped
	 * @param	Intger	$amountDays			amount of days to collect events to
	 * @return	Array
	 */
	public static function groupHolidaysByDays(array $holidays) {
		$holidaysGrouped	= array();

		foreach($holidays as $holiday) {
			$dateKey	= date('Ymd', $holiday['date']);

			$holidaysGrouped[$dateKey][] = $holiday;
		}

		return $holidaysGrouped;
	}

}
?>