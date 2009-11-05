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
 * HolidaySet manager
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuHolidaySetManager {

	const TABLE = 'ext_calendar_holidayset';


	/**
	 * Get holiday set
	 *
	 * @param	Integer		$idHolidaySet
	 * @return	TodoyuHolidaySet
	 */
	public static function getHolidaySet($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		return TodoyuCache::getRecord('TodoyuHolidaySet', $idHolidaySet);
	}



	/**
	 * Get all holidaySet records
	 *
	 * @return	Array
	 */
	public static function getAllHolidaySets() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'title';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Save a holidaySet
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function saveHolidaySet(array $data) {
		$idHolidaySet	= intval($data['id']);
		$xmlPath		= 'ext/calendar/config/form/holidayset.xml';

		if( $idHolidaySet === 0 ) {
			$idHolidaySet = self::addHolidaySet();
		}

		$data	= self::saveHolidaySetForeignData($data, $idHolidaySet);
		$data	= TodoyuFormHook::callSaveData($xmlPath, $data, $idHolidaySet);

		self::updateHolidaySet($idHolidaySet, $data);

		return $idHolidaySet;
	}



	/**
	 * Save holidaySet foreign data
	 *
	 * @param	Array		$data
	 * @param	Integer		$idHolidaySet
	 * @return	Array
	 */
	public static function saveHolidaySetForeignData(array $data, $idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		self::removeHolidays($idHolidaySet);

		if( is_array($data['holiday']) ) {
			$holidayIDs	= TodoyuArray::getColumn($data['holiday'], 'id');
			foreach($holidayIDs as $idHoliday) {
				self::addHoliday($idHolidaySet, $idHoliday);
			}
		}
		unset($data['holiday']);

		return $data;
	}



	/**
	 * Add a holidaySet record
	 *
	 * @param	Array		$data
	 * @return	Integer
	 */
	public static function addHolidaySet(array $data = array()) {
		unset($data['id']);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}



	/**
	 * Update holidaySet record
	 *
	 * @param	Integer		$idHolidaySet
	 * @param	Array		$data
	 * @return	Bool
	 */
	public static function updateHolidaySet($idHolidaySet, array $data) {
		$idHolidaySet	= intval($idHolidaySet);
		unset($data['id']);

		return Todoyu::db()->updateRecord(self::TABLE, $idHolidaySet, $data);
	}



	/**
	 * Add/link a holiday to the holidaySet
	 *
	 * @param	Integer		$idHolidaySet
	 * @param	Integer		$idHoliday
	 */
	public static function addHoliday($idHolidaySet, $idHoliday) {
		$idHolidaySet	= intval($idHolidaySet);
		$idHoliday		= intval($idHoliday);

		TodoyuDbHelper::addMMrelation('ext_calendar_mm_holiday_holidayset', 'id_holidayset', 'id_holiday', $idHolidaySet, $idHoliday);
	}



	/**
	 * Delete a holidaySet
	 *
	 * @param	Integer		$idHolidaySet
	 * @return	Bool
	 */
	public static function deleteHolidaySet($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		return Todoyu::db()->deleteRecord(self::TABLE, $idHolidaySet);
	}



	/**
	 * Remove/unlink all linked holidays (only the link)
	 *
	 * @param	Integer		$idHolidaySet
	 */
	public static function removeHolidays($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		TodoyuDbHelper::removeMMrelations('ext_calendar_mm_holiday_holidayset', 'id_holidayset', $idHolidaySet);
	}



	/**
	 * Get holidays linked to the holidaySet
	 *
	 * @param	Integer		$idHolidaySet
	 * @return	Array
	 */
	public static function getHolidays($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		$fields	= '	h.*';
		$table	= '	ext_calendar_holiday h,
					ext_calendar_mm_holiday_holidayset mm';
		$where	= '	mm.id_holidayset	= ' . $idHolidaySet . ' AND
					mm.id_holiday		= h.id AND
					h.deleted			= 0';
		$order	= ' h.date';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}



	/**
	 * Get all holidaySet records for admin
	 *
	 * @return	Array
	 */
	public static function getRecords() {
		$holidaySets= self::getAllHolidaySets();
		$records	= array();

		foreach($holidaySets as $holidaySet) {
			$records[] = array(
				'id'					=> $holidaySet['id'],
				'label'					=> $holidaySet['title'],
				'additionalInformations'=> $holidaySet['description']
			);
		}

		return $records;
	}



	/**
	 * Autocomplete holidaySets
	 *
	 * @param	String		$sword
	 * @return	Array
	 */
	public static function autocompleteHolidaySet($sword)	{
		$swords 	= TodoyuDiv::trimExplode(' ', $sword, true);
		$results	= array();

		if( sizeof($swords) > 0 ) {
			$fields	= 'id, name';
			$table	= self::TABLE;
			$where	= Todoyu::db()->buildLikeQuery($swords, array('name', 'description'));
			$order	= 'name';
			$limit	= 30;

			$holidaySets = Todoyu::db()->getArray($fields, $table, $where, '', $order, $limit);

			foreach($holidaySets as $holidaySet) {
				$results[$holidaySet['id']] = $holidaySet['name'];
			}
		}

		return $results;
	}

}


?>