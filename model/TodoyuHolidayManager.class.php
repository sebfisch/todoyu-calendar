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
 * Holiday (and holidaysets) Manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
*/

class TodoyuHolidayManager {

	const TABLE		= 'ext_calendar_holiday';
	const SET_TABLE	= 'ext_calendar_holidayset';


	/**
	 * Get holiday
	 *
	 * @param	Integer		$idHoliday
	 * @return	TodoyuHoliday
	 */
	public static function getHoliday($idHoliday) {
		$idHoliday	= intval($idHoliday);

		return TodoyuCache::getRecord('TodoyuHoliday', $idHoliday);
	}


	public static function getAllHolidays() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'title';

	}




	/**
	 * Saves a holiday record
	 *
	 *
	 * @param	Array	$formData
	 * @param	String	$formXML
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


		TodoyuDebug::printInFirebug($data, 'holiday');

		self::updateHoliday($idHoliday, $data);

		return $idRecord;
	}


	public static function addHoliday(array $data = array()) {
		unset($data['id']);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}


	public static function updateHoliday($idHoliday, array $data) {
		$idHoliday	= intval($idHoliday);
		unset($data['id']);

		return Todoyu::db()->updateRecord(self::TABLE, $idHoliday, $data);
	}



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
	 * Handles foreign records of holidays
	 *
	 * @param	Integer	$recordID
	 * @param	Array	$formData
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



	public static function removeHolidaySets($idHoliday) {
		$idHoliday	= intval($idHoliday);

		TodoyuDbHelper::removeMMrelations('ext_calendar_mm_holiday_holidayset', 'id_holiday', $idHoliday);
	}


	public static function addHolidaySet($idHoliday, $idHolidaySet) {
		$idHoliday		= intval($idHoliday);
		$idHolidaySet	= intval($idHolidaySet);

		TodoyuDbHelper::addMMrelation('ext_calendar_mm_holiday_holidayset', 'id_holiday', 'id_holidayset', $idHoliday, $idHolidaySet);
	}




	/**
	 * Sets the Holiday with given id to deleted
	 *
	 *
	 * @param	Integer	$holidayID
	 */
	public static function deleteHoliday($idHoliday)	{
		$idHoliday	= intval($idHoliday);

		return Todoyu::db()->deleteRecord(self::TABLE, $idHoliday);
	}




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
	 * Renders holidaysets in admin module
	 *
	 *
	 * @return	Array
	 */
	public static function getAdminHolidayList() {


		$returnArray = array();

		$holidays = Todoyu::db()->getArray('id, title, date', 'ext_calendar_holiday', 'deleted = 0', '', 'date DESC');

		foreach($holidays as $key => $holiday)	{
			$returnArray[] = array(
			'id'						=> $holiday['id'],
			'label'						=> $holiday['title'],
			'additionalInformations' 	=> date(TodoyuLocale::getLabel('core.dateFormat'), $holiday['date']));
		}

		return $returnArray;
	}















	/**
	 * Get IDs of holidaysets of given addresses (IDs)
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

		$holidaysetIDs	= array();

		if (! $groupAddressesBySet) {
				// Just get the holidayset IDs
			foreach($res as $entry) {
				$holidaysetIDs[]	= $entry['id_holidayset'];
			}
			$holidaysetIDs = array_unique($holidaysetIDs);
		} else {
				// Get an array of the address IDs with the IDs of their assigned holidaysets
			foreach($res as $entry) {
				$holidaysetIDs[ $entry['id'] ][]	= $entry['id_holidayset'];
			}
		}

		return $holidaysetIDs;
	}



	/**
	 * Get available holiday sets (to render select-dropdown)
	 *
	 * @return	Array
	 */
	public static function getHolidaysetOptions(TodoyuFormElement $field) {
		$holidaySets= TodoyuHolidaySetManager::getAllHolidaySets();
		$reform		= array(
			'id'	=> 'value',
			'name'	=> 'label'
		);

		return TodoyuArray::reform($holidaySets, $reform);
	}



	/**
	 * Get holidays of given sets in given timespan.
	 *
	 * @param	Intger	$tstampFrom			UNIX timestamp of day at beginning of timespan
	 * @param	Intger	$tstampUntil		UNIX timestamp of day at ending of timespan
	 * @param	Array	$holidaysetIDs
	 */
	public static function getHolidaysInTimespan($dateStart = 0, $dateEnd = 0, array $holidaysetIDs) {
		$holidaysetIDs	= TodoyuArray::intval($holidaysetIDs, true, false);
		$dateStart		= intval($dateStart);
		$dateEnd		= intval($dateEnd);

		if( sizeof($holidaysetIDs) === 0 ) {
			return array();
		}

		$fields	= '	h.*';
		$table	= 	self::TABLE . ' h,
					ext_calendar_mm_holiday_holidayset hhmm';
		$where	= '	h.id		= hhmm.id_holiday AND
					h.deleted	= 0 AND
					hhmm.id_holidayset IN(' . implode(',', $holidaysetIDs) . ') AND
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
		$holidaysetIDs	= self::getHolidaySetsOfAddresses($addressIDs);

			// Get all holidays affected holidaysets in given timespan
		$holidays		= self::getHolidaysInTimespan($dateStart, $dateEnd, $holidaysetIDs);

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
			if($swordArray[0] != '*')	{
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
	 * Autocomplete holidaysets
	 *
	 *
	 * @param	String	$sword
	 * @return	Array
	 */
	public static function autocompleteHolidaySet($sword)	{
		$swordArray = TodoyuDiv::trimExplode(' ', $sword, true);
		$results	= array();

		if(count($swordArray) > 0)	{
			$where = '';
			if($swordArray[0] != '*')	{
				$where = Todoyu::db()->buildLikeQuery($swordArray, array('name', 'description'));
			}

			$res = Todoyu::db()->doSelect('id, name', self::SET_TABLE, $where, '', 'name DESC');

			while($row = Todoyu::db()->fetchAssoc($res))	{
				$results[$row['id']] = $row['name'];
			}

			return $results;

		} else {
			return array();
		}
	}



	/**
	 * Saves holiday sets to the database
	 *
	 * @param	Array	$formData
	 * @param	String	$formXML
	 * @return	Integer
	 */
	public static function saveHolidaySets(array $formData, $formXML)	{
		$idRecord = intval($formData['id']);
		unset($formData['id']);

		if($idRecord == 0)	{
			$idRecord = self::createNewRecord(self::SET_TABLE);
		}

		$formData	= TodoyuFormHook::callSaveData($formXML, $formData, $idRecord);
		$formData	= self::handleHolidaySetForeignRecords($idRecord, $formData);

		$where = 'id = ' . $idRecord;

		Todoyu::db()->doUpdate(self::SET_TABLE, $where, $formData);

		return $idRecord;
	}





	/**
	 * Creates a new empty holidy record and returns its id
	 *
	 *
	 * @return	Integer
	 */
	protected static function createNewRecord($table)	{
		$insertArray = array(
			'date_create'		=> NOW,
			'date_update'		=> NOW,
			'deleted'			=> 0,
			'id_user_create'	=> userid()
		);

		return Todoyu::db()->doInsert($table, $insertArray);
	}



	/**
	 * Handles foreign records of holidaysets
	 *
	 *
	 * @param	Integer	$recordID
	 * @param	Array	$formData
	 * @return	Array
	 */
	protected static function handleHolidaySetForeignRecords($idRecord, array $formData)	{
		$localData = $formData;

		self::removeMMData($idRecord, 'ext_calendar_mm_holiday_holidayset', 'id_holidayset');
		if(array_key_exists('holiday', $localData))	{
			$holidays = array();

			foreach($localData['holiday'] as $holiday)	{
				$holidays[] = $holiday['id'];
			}

			self::saveMMData($idRecord, $holidays, 'ext_calendar_mm_holiday_holidayset', 'id_holiday', 'id_holidayset');

			unset($localData['holiday']);
		}

		return $localData;
	}





	/**
	 * Sets the Holiday set with given id to deleted
	 *
	 *
	 * @param	Integer	$holidaySetID
	 */
	public static function removeHolidaySet($holidaySetID)	{
		$updateArray	= array('date_update' => NOW,
								'deleted' => 1);

		Todoyu::db()->doUpdate(self::SET_TABLE, 'id = ' . intval($holidaySetID), $updateArray);
	}





	/**
	 * Removes all entries of given table which contains given id
	 *
	 *
	 * @param	Integer	$recordID
	 * @param	String	$mmTable
	 */
	protected static function removeMMData($idRecord, $mmTable, $localDataBaseField)	{
		$where = $localDataBaseField . ' = ' . intval($idRecord);

		Todoyu::db()->doDelete($mmTable, $where);
	}



	/**
	 * Saves mm data to given table
	 *
	 *
	 * @param	Integer	$recordID
	 * @param	Array	$foreignRecordsIDs
	 * @param	String	$mmTable
	 * @param	String	$foreignMMField
	 */
	protected static function saveMMData($idRecord, array $foreignRecordsIDs, $mmTable, $foreignMMField, $localMMField)	{
		foreach($foreignRecordsIDs as $id)	{
			$insertArray = array(
				$localMMField	=> $idRecord,
				$foreignMMField	=> $id
			);

			Todoyu::db()->doInsert($mmTable, $insertArray);
		}
	}



	/**
	 * Gets the holiday label from given ID
	 *
	 *
	 * @param	Integer	$holidayID
	 * @return	String
	 */
	public static function getHolidayLabel($holidayID)	{
		$holidayID	= intval($holidayID);

		$holiday = Todoyu::db()->getArray('title', self::TABLE, 'id = ' . $holidayID);

		return $holiday[0]['title'];
	}



	/**
	 * Gets the holidayset label from given ID
	 *
	 *
	 * @param	Integer	$holidaySetID
	 * @return	string
	 */
	public static function getHolidaySetLabel($holidaySetID)	{
		$holidaySetID	= intval($holidaySetID);

		$holidaySet 	= Todoyu::db()->getArray('name', self::SET_TABLE, 'id = ' . $holidaySetID);

		return $holidaySet[0]['name'];
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