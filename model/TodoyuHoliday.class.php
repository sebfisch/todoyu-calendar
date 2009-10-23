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
	 * @param	Integer		$idHoliday
	 */
	function __construct($idHoliday)	{
		$idHoliday	= intval($idHoliday);

		parent::__construct($idHoliday, self::TABLE );
	}



	/**
	 * Load foreign data
	 */
	public function loadForeignData()	{
		$this->data['holidayset']	= TodoyuHolidayManager::getHolidaySets($this->id);
	}



	/**
	 * Get template data for holiday
	 *
	 * @param	Bool		$loadForeignRecords
	 * @return	Array
	 */
	public function getTemplateData($loadForeignRecords = false) {
		if( $loadForeignRecords ) {
			$this->loadForeignData();
		}

		return parent::getTemplateData();
	}

}

?>