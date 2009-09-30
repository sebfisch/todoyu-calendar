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
 * HolidaySet
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuHolidaySet extends TodoyuBaseObject {

	const TABLE = 'ext_calendar_holidayset';

	/**
	 * Constructor
	 *
	 * @param	Integer	$recordID
	 */
	function __construct($idRecord)	{
		$idRecord	= intval($idRecord);
		parent::__construct($idRecord, self::TABLE);
	}



	/**
	 * Load foreign data (holidays)
	 *
	 */
	public function loadForeignData()	{
		$this->loadHolidays();
	}



	/**
	 * Load holidays
	 *
	 */
	private function loadHolidays()	{
		$localID	= intval( $this->data['id'] );
		$holidays	= array();

		$result = Todoyu::db()->doSelect(
			'id_holiday',							// fields
			'ext_calendar_mm_holiday_holidayset',	// table
			'id_holidayset = '.intval($localID)		// where clause
		);

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$obj = new Holiday($row['id_holiday']);
			$holidays[] = $obj->getTemplateData();
		}

		$this->data['holiday'] = $holidays;
	}


}
?>