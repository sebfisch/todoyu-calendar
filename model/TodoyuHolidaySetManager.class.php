<?php

class TodoyuHolidaySetManager {

	const TABLE = 'ext_calendar_holidayset';


	public static function getHolidaySet($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		return TodoyuCache::getRecord('TodoyuHolidaySet', $idHolidaySet);
	}

	public static function getAllHolidaySets() {
		$fields	= '*';
		$table	= self::TABLE;
		$where	= 'deleted = 0';
		$order	= 'title';

		return Todoyu::db()->getArray($fields, $table, $where, '', $order);
	}


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

	public static function addHolidaySet(array $data = array()) {
		unset($data['id']);

		return Todoyu::db()->addRecord(self::TABLE, $data);
	}


	public static function updateHolidaySet($idHolidaySet, array $data) {
		$idHolidaySet	= intval($idHolidaySet);
		unset($data['id']);

		return Todoyu::db()->updateRecord(self::TABLE, $idHolidaySet, $data);
	}

	public static function addHoliday($idHolidaySet, $idHoliday) {
		$idHolidaySet	= intval($idHolidaySet);
		$idHoliday		= intval($idHoliday);

		TodoyuDbHelper::addMMrelation('ext_calendar_mm_holiday_holidayset', 'id_holidayset', 'id_holiday', $idHolidaySet, $idHoliday);
	}


	public static function deleteHolidaySet($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		return Todoyu::db()->deleteRecord(self::TABLE, $idHolidaySet);
	}


	public static function removeHolidays($idHolidaySet) {
		$idHolidaySet	= intval($idHolidaySet);

		TodoyuDbHelper::removeMMrelations('ext_calendar_mm_holiday_holidayset', 'id_holidayset', $idHolidaySet);
	}


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

}



?>