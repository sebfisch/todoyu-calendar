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
 * Holiday
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */

class TodoyuHoliday extends TodoyuBaseObject {

	const TABLE = 'ext_calendar_holiday';

	/**
	 * Constructor
	 *
	 * @param	Integer	$recordID
	 *
	 */
	function __construct($idRecord)	{
		$idRecord	= intval($idRecord);

		parent::__construct($idRecord, self::TABLE );
	}



	/**
	 * Load foreign data
	 *
	 *
	 */
	public function loadForeignData()	{
		$this->loadHolidaySets();
	}



	/**
	 * Load holidaysets
	 *
	 *
	 */
	private function loadHolidaySets()	{
		$localID		= intval( $this->data['id'] );
		$holidaySets	= array();

		$result = Todoyu::db()->doSelect('id_holidayset', 'ext_calendar_mm_holiday_holidayset', 'id_holiday = '.intval($localID));

		while($row = Todoyu::db()->fetchAssoc($result))	{
			$obj = new TodoyuHolidaySet($row['id_holidayset']);
			$holidaySets[] = $obj->getTemplateData();
		}

		$this->data['holidayset'] = $holidaySets;
	}

}

?>