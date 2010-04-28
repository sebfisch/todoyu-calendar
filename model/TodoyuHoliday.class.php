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
 * Holiday
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuHoliday extends TodoyuBaseObject {

	/**
	 * Default table for database requests
	 */
	const TABLE = 'ext_calendar_holiday';



	/**
	 * Constructor
	 *
	 * @param	Integer		$idHoliday
	 */
	public function __construct($idHoliday)	{
		$idHoliday	= intval($idHoliday);

		parent::__construct($idHoliday, self::TABLE );
	}



	/**
	 * Get holiday label
	 *
	 * @return	String
	 */
	public function getLabel() {
		return $this->get('title');
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
	 * @param	Boolean		$loadForeignRecords
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