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
 * HolidaySet
 *
 * @package		Todoyu
 * @subpackage	Calendar
 */
class TodoyuHolidaySet extends TodoyuBaseObject {

	/**
	 * @var	String		Default table for database requests
	 */
	const TABLE = 'ext_calendar_holidayset';



	/**
	 * Constructor
	 *
	 * @param	Integer	$recordID
	 */
	function __construct($idHolidaySet)	{
		$idHolidaySet	= intval($idHolidaySet);

		parent::__construct($idHolidaySet, self::TABLE);
	}



	/**
	 * Load foreign data (holidays)
	 */
	public function loadForeignData()	{
		$this->data['holiday']	= TodoyuHolidaySetManager::getHolidays($this->id);
	}



	/**
	 * Get template data for holiday set
	 * 
	 * @param	Boolean	$loadForeignRecords
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